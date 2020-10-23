<?
//================================
// log setting
//================================

ini_set('log_errors','on');
ini_set('error_log','php.log');

// debug
$debug_flg = true;

function debug($str){
    global $debug_flg;
if(!empty($debug_flg)) error_log('デバッグ'.$str);
}


//================================
// session setting
//================================

// session格納ディレクトリ変更(tmp下は30日保管可能)
session_save_path("var/tmp/");

// ガベージコレクション有効期限変更
ini_set('session.gc_maxlifetime', 60*60*24*30);

// cookie延長
ini_set('session.cookie_lifetime', 60*60*24*30);

session_start();
// セッション再生成
session_regenerate_id();


