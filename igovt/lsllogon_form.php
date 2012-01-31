<?php

require_once("$CFG->libdir/formslib.php");

class lsllogon_form extends moodleform {

    function definition() {
        global $CFG;

        $mform =& $this->_form;
        $mform->addElement('html', '<div class="loginbox onecolumn clearfix"><div class="loginpanel"><h2>'.get_string('lsllogonheading', 'auth_igovt').'</h2><div class="subcontent"><div class="desc">'.get_string('lsllogondesc', 'auth_igovt').'</div>');

        $mform->addElement('text', 'email', get_string('email'), 'maxlength="255" size="25" ');
        $mform->setType('email', PARAM_TEXT);
        $mform->addRule('email', null, 'required', null, 'client');

        $mform->addElement('text', 'accesscode', get_string('accesscode', 'auth_igovt'), 'maxlength="100" size="25" ');
        $mform->setType('accesscode', PARAM_TEXT);
        $mform->addRule('accesscode', null, 'required', null, 'client');

        $mform->addElement('submit', 'submit', get_string('validatecode', 'auth_igovt'));

        $mform->addElement('html', '</div><br /></div></div></div>');
        $mform->addElement('html', '<div class="center"><strong>'.get_string('needaccesscode', 'auth_igovt').'</strong><blockquote><a href="request.php">'.get_string('requestinvite', 'auth_igovt').'</blockquote></a>');
        $mform->addElement('html', get_string('logonhelp', 'auth_igovt'));
    }

    function validation($data) {
        global $CFG;

        $errors = array();
        $config = get_config('auth/igovt');

        // Check for valid email + access code combo
        $sql = "SELECT u.id, u.firstname, u.lastname, i.accesscodehash, i.status, i.attempts
                FROM {$CFG->prefix}user u
                    INNER JOIN {$CFG->prefix}auth_igovt_invitations i ON u.id = i.userid
                WHERE LOWER(u.email) = '".strtolower(trim($data['email']))."'";

        if (!$user = get_record_sql($sql)) {
            $errors['accesscode'] = get_string('emailoraccessnotfound', 'auth_igovt');
            return($errors);
        }

        $igovt = get_auth_plugin('igovt');

        if ($user->accesscodehash != sha1(trim($data['accesscode']))) {

            if ($user->attempts + 1 >= $config->invitemaxtries) {
                $igovt->notify_logonfailure('locked', $user);
 
                $status = 'locked';
                $errors['accesscode'] = get_string('accesscodelocked', 'auth_igovt');
            } else {
                $status = 'invited';

                $errors['accesscode'] = get_string('emailoraccessnotfound', 'auth_igovt');
            }

            // Bump login attempt count
            $sql = "UPDATE {$CFG->prefix}auth_igovt_invitations
                    SET attempts = attempts + 1, status = '$status'
                    WHERE userid = ".$user->id." AND status = 'invited'";
            execute_sql($sql, false);

        } else {
            // Check for expiry
            if ($user->status == 'expired') {
                $errors['accesscode'] = get_string('accesscodeexpired', 'auth_igovt');
                $igovt->notify_logonfailure('expired', $user);
            }

            if ($user->status == 'locked') {
                $errors['accesscode'] = get_string('accesscodelocked', 'auth_igovt');
                $igovt->notify_logonfailure('locked', $user);
            }

            if ($user->status == 'cancelled') {
                $errors['accesscode'] = get_string('accesscodecancelled', 'auth_igovt');
                $igovt->notify_logonfailure('cancelled', $user);
            }

            if ($user->status == 'validated') {
                $errors['accesscode'] = get_string('accesscodeused', 'auth_igovt');
            }
        }
        // Check for lockout
        return($errors);
    }
}
?>
