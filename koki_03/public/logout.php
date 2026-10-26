<?php
# ここからセッションの独自実装 (詳細は後期第2回授業参照) ##############
// セッションIDの取得(なければ新規で作成&設定)
$session_cookie_name = 'session_id';
$session_id = $_COOKIE[$session_cookie_name] ?? base64_encode(random_bytes(64));
if (!isset($_COOKIE[$session_cookie_name])) {
  setcookie($session_cookie_name, $session_id);
}
// 接続 (redisコンテナの6379番ポートに接続)
$redis = new Redis();
$redis->connect('redis', 6379);
// Redisにセッション変数を保存しておくキー
$redis_session_key = "session-" . $session_id;
// Redisからセッションのデータを読み込み
// 既にセッション変数(の配列)が何かしら格納されていればそれを，なければ空の配列を $session_values変数に保存
$session_values = $redis->exists($redis_session_key)
  ? json_decode($redis->get($redis_session_key), true)
  : [];
# セッション共通処理ここまで ###################################################

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // POSTで送られてきた場合のみログアウト処理
  unset($session_values["login_user_id"]);
  $redis->set($redis_session_key, json_encode($session_values));

  // ログアウトが完了したらログアウト完了画面にリダイレクト
  header("HTTP/1.1 303 See Other");
  header("Location: ./logout_finish.php");
  return;
}
?>

<h1>ログアウト</h1>
<!-- ログアウトフォーム -->
<form method="POST">
  <button type="submit">ログアウト</button>
</form>
