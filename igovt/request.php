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
require('request_form.php');
require('auth.php');

// User is already logged in
if (isloggedin()) {
    redirect($CFG->wwwroot);
    exit;
}

/// Define variables used in page
if (!$site = get_site()) {
    error("No site found!");
}

$auth = new auth_plugin_igovt;

if (empty($CFG->langmenu)) {
    $langmenu = "";
} else {
    $currlang = current_language();
    $langs    = get_list_of_languages();
    $langlabel = get_accesshide(get_string('language'));
    $langmenu = popup_form ("$CFG->httpswwwroot/login/index.php?lang=", $langs, "chooselang", $currlang, "", "", "", true, 'self', $langlabel);
}


$requestinvite = get_string('requestinvite', 'auth_igovt');

$navlinks = array(array('name' => $requestinvite, 'link' => null, 'type' => 'misc'));
$navigation = build_navigation($navlinks);


$mform = new request_form;

print_header("$site->fullname: $requestinvite", $site->fullname, $navigation, '',
        '', true, '<div class="langmenu">'.$langmenu.'</div>');


if ($data = $mform->get_data()) {
    // Find user and any existing invitation
    $sql = "SELECT u.*, i.id AS inviteid, i.datecreated, i.status
            FROM {$CFG->prefix}user u
            LEFT JOIN {$CFG->prefix}auth_igovt_invitations i ON u.id = i.userid
            WHERE u.deleted = 0 AND LOWER(u.email) = '".strtolower(trim($data->email))."' AND u.auth = 'igovt' ";

    if ($user = get_record_sql($sql)) {
        if (!empty($user->inviteid)) {
            if ($user->status != 'validated' && ($user->datecreated < time() - 300)) { // Limit to one request per 5 minutes
                delete_records('auth_igovt_invitations', 'id', $user->inviteid);
                $auth->inviteuser($user);
            }
        } else {
            $auth->inviteuser($user);
        }
    }

    display_confirmation();

} else {
    $mform->display();
}



print_footer();

function display_confirmation() {

    echo '<div class="loginbox onecolumn clearfix">
            <div class="loginpanel">
                <h2>'.get_string('requestinvitelong', 'auth_igovt').'</h2>
                <div class="subcontent">
                    <div class="desc">'.get_string('requestsent', 'auth_igovt').'</div>
                    <br /><br /><br />';
    if (isset($_SESSION['flt'])) {
        echo '<a href="lsllogon.php">'.get_string('backtolsllogon', 'auth_igovt').'</a>';
    } else {
        echo '<a href="logon.php">'.get_string('backtologon', 'auth_igovt').'</a>';
    }

    echo '
                </div>
            </div>
          </div>';
}
