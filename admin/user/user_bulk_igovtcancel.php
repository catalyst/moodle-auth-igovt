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
    $sql = "UPDATE {$CFG->prefix}auth_igovt_invitations 
            SET status = 'cancelled', datechanged = ".time()."
            WHERE userid in ($in)";
    execute_sql($sql, false);
    redirect($return, get_string('igovtinvitationscancelled', 'auth_igovt'));

} else {
    $in = implode(',', $SESSION->bulk_users);
    $userlist = get_records_select_menu('user', "id IN ($in)", 'fullname', 'id,'.sql_fullname().' AS fullname');
    $usernames = implode(', ', $userlist);
    $optionsyes = array();
    $optionsyes['confirm'] = 1;
    $optionsyes['sesskey'] = sesskey();
    print_heading(get_string('confirmation', 'admin'));
    notice_yesno(get_string('igovtcancelcheckfull', 'auth_igovt', $usernames), 'user_bulk_igovtcancel.php', 'user_bulk.php', $optionsyes, NULL, 'post', 'get');
}

admin_externalpage_print_footer();
?>
