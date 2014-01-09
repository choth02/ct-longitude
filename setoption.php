<?php
require_once 'globals.php';

if (!isset($_REQUEST['oauth']['token']) || !validateGoogleOauthToken($_REQUEST['oauth']['token'])) {
    $res['status'] = 'error';
    $res['error'] = 'no authenticated user';
    goto end;
}
$token = $_REQUEST['oauth']['token'];
$userid = $_SESSION[$token]['user_id'];

if (!isset($_REQUEST['option'])) {
    $res['status'] = 'error';
    $res['error'] = 'no option given';
    goto end;
}

if (!isset($_REQUEST['value'])) {
    $res['status'] = 'error';
    $res['error'] = 'no value given';
    goto end;
}

$option = filter_var($_REQUEST['option'], FILTER_SANITIZE_STRING);
$value = $_REQUEST['value'];

if ($dbh) {
    switch ($option) {
        case 'name':
            $v = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
            $q = "UPDATE `buddies` SET `name` = '$v' WHERE `userid` = '$userid'";
            $dbh->exec($q);
            $res['status'] = 'ok';
            $res['userid'] = $userid;
            $res['option'] = $option;
            $res['value'] = $v;
            break;
        case 'sharetracks':
            $v = in_array($value, array('1', 'ok', 'yes', 'true')) ? 1 : 0;
            $q = "UPDATE `buddies` SET `sharetracks` = $v WHERE `userid` = '$userid'";
            $dbh->exec($q);
            $res['status'] = 'ok';
            $res['userid'] = $userid;
            $res['option'] = $option;
            $res['value'] = $v;
            break;
        case 'avatar':
            if (strpos($value, 'data:image/png;base64,') === 0) {
                $value = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
                $q = "UPDATE `buddies` SET `avatar` = '$value' WHERE `userid` = '$userid'";
                $dbh->exec($q);
                $res['status'] = 'ok';
                $res['userid'] = $userid;
                $res['option'] = $option;
                $res['value'] = '<...present...>';
            }
            else {
                $res['status'] = 'error';
                $res['error'] = 'value is not a data URL';
            }
            break;
        default:
            $res['status'] = 'error';
            $res['error'] = 'invalid option: ' + $option;
            break;
    }
}
else {
    $res['status'] = 'error';
    $res['error'] = $dbh->errorInfo();
}

end:
echo json_encode($res);

?>