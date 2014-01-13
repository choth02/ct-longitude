<?php
require_once 'globals.php';

if (!isset($_REQUEST['oauth']['token']) || !validateGoogleOauthToken($_REQUEST['oauth']['token'])) {
    $res['status'] = 'error';
    $res['error'] = 'Ung�ltige Authentifizierungsdaten: OAuth-Token fehlt oder ist falsch.';
    goto end;
}
$token = $_REQUEST['oauth']['token'];
$userid = $_SESSION[$token]['user_id'];


if ($dbh) {
    $q = "SELECT `sharetracks`, `avatar`, `name` FROM `buddies` WHERE `userid` = '$userid'";
    $sth = $dbh->prepare($q);
    $sth->execute();
    $row = $sth->fetch();
    if (!$row) {
        $dbh->exec("INSERT INTO `buddies` (`userid`, `sharetracks`) VALUES('$userid', 0)");
        $sth->execute();
        $row = $sth->fetch();
        $res['info'] = 'user added';
    }
    $res['sharetracks'] = intval($row[0]) != 0 ? 'true' : 'false';
    $res['avatar'] = $row[1];
    $res['userid'] = $userid;
    $res['name'] = $row[2];
    $res['status'] = 'ok';
    
    $q = "SELECT `lat`, `lng` FROM `locations` WHERE `userid` = '$userid' ORDER BY `timestamp` DESC LIMIT 1";
    $rows = $dbh->query($q);
    $row = $rows->fetch();
    if ($row) {
        $res['lat'] = floatval($row[0]);
        $res['lng'] = floatval($row[1]);
    }
    $res['processing_time'] = round(microtime(true) - $T0, 3);
}

end:
echo json_encode($res);
?>
