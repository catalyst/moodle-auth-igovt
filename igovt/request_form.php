<?php

require_once("$CFG->libdir/formslib.php");

class request_form extends moodleform {

    function definition() {
        global $CFG;

        $mform =& $this->_form;
        $mform->addElement('html', '<div class="loginbox onecolumn clearfix"><div class="loginpanel"><h2>'.get_string('requestinvitelong', 'auth_igovt').'</h2><div class="subcontent"><div class="desc">'.get_string('requestinvitedesc', 'auth_igovt').'</div>');

        $mform->addElement('text', 'email', get_string('email'), 'maxlength="255" size="35" ');
        $mform->setType('email', PARAM_TEXT);
        $mform->addRule('email', null, 'required', null, 'client');

        $mform->addElement('submit', 'submit', get_string('requestinvite', 'auth_igovt'));

        $mform->addElement('html', '</div><br /><br /> <br />'.get_string('requestinvitepost', 'auth_igovt').'<br /></br />');
        if ($_SESSION['flt']) {
            $mform->addElement('html', '<a href="lsllogon.php">'.get_string('backtolsllogon', 'auth_igovt').'</a><br /><br />');
        } else {
            $mform->addElement('html', '<a href="logon.php">'.get_string('backtologon', 'auth_igovt').'</a><br /><br />');
        }
        $mform->addElement('html', get_string('logonhelp', 'auth_igovt'). '</div></div>');
    }

}
?>
