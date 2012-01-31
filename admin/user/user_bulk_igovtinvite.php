<?php
require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$confirm = optional_param('confirm', 0, PARAM_BOOL);

admin_externalpage_setup('userbulk');
require_capability('moodle/user:update', get_context_instance(CONTEXT_SYSTEM));

$return = $CFG->wwwroot.'/'.$CFG->admin.'/user/user_bulk.php';

if (empty($SESSION->bulk_users)) {
    redirect($return);
}

admin_externalpage_print_header();

if ($confirm and confirm_sesskey()) {
    $in = implode(',', $SESSION->bulk_users);
    $sql = "SELECT u.*, i.status FROM {$CFG->prefix}user u
            LEFT JOIN {$CFG->prefix}auth_igovt_invitations i ON i.userid = u.id
            WHERE u.id IN ($in)";


    if ($rs = get_recordset_sql($sql)) {

        $auth = get_auth_plugin('igovt');
        while ($user = rs_fetch_next_record($rs)) {
            if ($user->status != 'invited') {
                delete_records('auth_igovt_invitations', 'userid', $user->id);

                if (!$auth->inviteuser($user)) {
                    notify(get_string('usernotinvited', '', fullname($user, true)));
                }
            } else {
                notify(get_string('useralreadyinvited', 'auth_igovt', fullname($user, true)));
            }
        }
        rs_close($rs);
    }
    redirect($return, get_string('igovtinvitationssent', 'auth_igovt'));

} else {
    $in = implode(',', $SESSION->bulk_users);
    $userlist = get_records_select_menu('user', "id IN ($in)", 'fullname', 'id,'.sql_fullname().' AS fullname');
    $usernames = implode(', ', $userlist);
    $optionsyes = array();
    $optionsyes['confirm'] = 1;
    $optionsyes['sesskey'] = sesskey();
    print_heading(get_string('confirmation', 'admin'));
    notice_yesno(get_string('igovtinvitecheckfull', 'auth_igovt', $usernames), 'user_bulk_igovtinvite.php', 'user_bulk.php', $optionsyes, NULL, 'post', 'get');
}

admin_externalpage_print_footer();
?>
