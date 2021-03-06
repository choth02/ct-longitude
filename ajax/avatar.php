<?php
require_once 'globals.php';

if (!isset($_REQUEST['oauth']['token']) || !isset($_REQUEST['oauth']['clientId']) || !validateGoogleOauthToken($_REQUEST['oauth']['token'], $_REQUEST['oauth']['clientId'])) {
    $res['status'] = 'authfailed';
    $res['error'] = 'Ungueltige Authentifizierungsdaten: OAuth-Token fehlt oder ist falsch.';
    goto end;
}
$token = $_REQUEST['oauth']['token'];
$userid = $_SESSION[$token]['user_id'];

if (!isset($_REQUEST['userid'])) {
    $res['status'] = 'error';
    $res['error'] = 'keine `userid` angegeben.';
    goto end;
}

$requested_userid = filter_var($_REQUEST['userid'], FILTER_SANITIZE_MAGIC_QUOTES);

if ($dbh) {
    $rows = $dbh->query("SELECT `avatar`, `name` FROM `buddies` WHERE `userid` = '$requested_userid'");
    $row = $rows->fetch();
    if ($row) {
        $res['avatar'] = $row[0];
        $res['name'] = $row[1];
        $res['status'] = 'ok';
        $res['userid'] = $requested_userid;
        $res['processing_time'] = processingTime();
    }
    else {
        $res['status'] = 'error';
        $res['error'] = "kein Eintrag f�r `$requested_userid` gefunden";
    }
}

end:
header('Content-Type: text/json');
echo json_encode($res);

?>
