<?php
//================================
// log setting
//================================
ini_set('log_errors', 'on');
ini_set('error_log', 'php.log');
// debug
$debug_flg = true;

function debug($str)
{
    global $debug_flg;
    if ($debug_flg) {
        error_log('デバッグ' . $str);
    }
}
//================================
// session setting
//================================
// session格納ディレクトリ変更(tmp下は30日保管可能)
// session_save_path("var/tmp/");
// ガベージコレクション有効期限変更
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 30);
// cookie延長
ini_set('session.cookie_lifetime', 60 * 60 * 24 * 30);
session_start();
// セッション再生成
session_regenerate_id();
//================================
// DB接続
//================================
try {

    $pdo = new PDO(
        'mysql:dbname=todo_list;host=localhost;charset=utf8mb4',
        'root',
        'root',
        array(
            // 例外処理設定
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // プリペアドステートメント (onだと文字列以外を扱う際にキャストが必要)
            PDO::ATTR_EMULATE_PREPARES => true,
        )
    );
} catch (Exception $e) {
    header('Content-Type: text/plain; charset=UTF-8', true, 500);
    $e->getMessage();
}

//================================
// バリデーション
//================================
// エラーメッセージ
$err_msg = array();
define('MSG00', 'エラーが発生しました。');
define('MSG01', '入力必須です');
define('MSG02', '255文字以内で入力してください');
define('MSG03', '5文字以上で入力してください');
define('MSG04', 'メールアドレス形式で入力してください');
define('MSG05', 'パスワードが一致しません');
define('MSG06', '半角英数字で入力してください');
define('MSG07', '既に登録済みのemailです。');

// 未入力チェック
function validRequired($str)
{
    global $err_msg;
    if (empty($str)) {
        $err_msg = MSG01;
        var_dump($err_msg);
    }
}
// 最大文字数チェック
function validMax($str, $max = 255)
{
    global $err_msg;
    if ($str > $max) {
        $err_msg = MSG02;
    }
}
// 最小文字数
function validMin($str, $min = 5)
{
    global $err_msg;
    if ($str <= $min) {
        $err_msg = MSG03;
    }
}
// メアド形式チェック
function validEmail($email)
{
    global $err_msg;
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err_msg = MSG04;
    }
}
// 同値チェック
function validMatch($pass, $pass_re)
{
    global $err_msg;
    if (!$pass === $pass_re) {
        $err_msg = MSG05;
    }
}
// 半角チェック
function validHalf($str)
{
    global $err_msg;
    if (!preg_match("/^[a-zA-Z0-9]+$/", $str)) {
        $err_msg = MSG06;
    }
}
// メアド重複チェック
function validEmailDup($email)
{
    global $err_msg;
    global $pdo;
    try {
        $stmt = $pdo->prepare('SELECT COUNT(*) from user WHERE email = :email AND delete_flg = 0');
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch();
        if (array_shift($result)) {
            $err_msg = MSG07;
            debug('email重複あり');
        }
    } catch (Exception $e) {
        header('Content-Type: text/plain; charset=UTF-8', true, 500);
        $e->getMessage();
    }
}
// ログイン認証
