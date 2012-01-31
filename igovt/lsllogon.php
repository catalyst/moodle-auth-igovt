<?php
/**
 * @author Matt Clarkson - forked as igovt plugin
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package auth/igovt
 * @version 1.0
 *
 * Authentication Plugin: Authentication plugin for New Zealand's igovt logon service
 *
**/

require('../../config.php');
require('lsllogon_form.php');

// User is already logged in
if (isloggedin()) {
    redirect($CFG->wwwroot);
    exit;
}

/// Define variables used in page
if (!$site = get_site()) {
    error("No site found!");
}

if (empty($_SESSION['auth_igovt_flt'])) {
    redirect($CFG->wwwroot);
}

$flt = $_SESSION['auth_igovt_flt'];

if (empty($CFG->langmenu)) {
    $langmenu = "";
} else {
    $currlang = current_language();
    $langs    = get_list_of_languages();
    $langlabel = get_accesshide(get_string('language'));
    $langmenu = popup_form ("$CFG->httpswwwroot/login/index.php?lang=", $langs, "chooselang", $currlang, "", "", "", true, 'self', $langlabel);
}


$logonsite = get_string('loginsite');
$logonalt = get_string('logonalt', 'auth_igovt');
$logonabout = get_string('logonabout', 'auth_igovt');
$logonaboutalt = get_string('logonaboutalt', 'auth_igovt');

$navlinks = array(array('name' => $logonsite, 'link' => null, 'type' => 'misc'));
$navigation = build_navigation($navlinks);

$auth = get_auth_plugin('igovt');
$mform = new lsllogon_form;

if ($data = $mform->get_data()) {

   // Get user from invitation
    $sql = "SELECT u.*, i.id AS inviteid
            FROM {$CFG->prefix}user u
            INNER JOIN {$CFG->prefix}auth_igovt_invitations i ON u.id = i.userid
            WHERE u.deleted = 0 AND LOWER(u.email) = '".strtolower(trim($data->email))."' AND i.accesscodehash = '".sha1(trim($data->accesscode))."'";

    if (!$user = get_record_sql($sql)) {
        error('Unable to load user record');
    }
    $user->username = $flt;

    // If dual login is not set then force users auth type to igovt
    if (empty($auth->config->duallogin)) {
        $user->auth = 'igovt';
    }

    update_record('user', addslashes_object($user));
    set_field('auth_igovt_invitations', 'status', 'validated', 'id', $user->inviteid);

    $USER = $user;      // Login the user
    redirect($CFG->wwwroot);

} else {
    print_header("$site->fullname: $logonsite", $site->fullname, $navigation, '',
            '', true, '<div class="langmenu">'.$langmenu.'</div>');
    echo '<link rel="stylesheet" type="text/css" href="./css/igovt_buttons.css" />';

    $mform->display();
    print_footer();
}

