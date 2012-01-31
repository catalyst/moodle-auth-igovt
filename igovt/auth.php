<?php
/**
 * @author Erlend Strømsvik - Ny Media AS
 * @author Piers Harding - made quite a number of changes
 * @author Tsukasa Hamano - coursecreator and group attribute mapping
 * @author Matt Clarkson - forked as igovt plugin
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package auth/igovt
 * @version 1.0
 *
 * Authentication Plugin: Authentication plugin for New Zealand's igovt logon service
 *
 * Based on plugins auth/saml plugin
 *
**/

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->libdir.'/authlib.php');

/**
 * igovt authentication plugin.
**/
class auth_plugin_igovt extends auth_plugin_base {

    /**
    * Constructor.
    */
    function auth_plugin_igovt() {
        $this->authtype = 'igovt';
        $this->config = get_config('auth/igovt');
        $this->log = '';
    }

    /**
    * Returns true if the username and password work and false if they are
    * wrong or don't exist.
    *
    * @param string $username The username (with system magic quotes)
    * @param string $password The password (with system magic quotes)
    * @return bool Authentication success or failure.
    */
    function user_login($username, $password) {
        // if true, user_login was initiated by saml/index.php
        if($GLOBALS['saml_login']) {
            unset($GLOBALS['saml_login']);
            return TRUE;
        }

        return FALSE;
    }


    /**
    * Returns the user information for 'external' users. In this case the
    * attributes provided by Identity Provider
    *
    * @return array $result Associative array of user data
    */
    function get_userinfo($username) {
        if($login_attributes = $GLOBALS['saml_login_attributes']) {
            $attributemap = $this->get_attributes();
            $attributemap['memberof'] = $this->config->memberattribute;
            $result = array();

            foreach ($attributemap as $key => $value) {
                if(isset($login_attributes[$value]) && $attribute = $login_attributes[$value][0]) {
                    $result[$key] = $attribute;
                } else {
                    $result[$key] = $value; // allows user to set a hardcode default
                }
            }
            return $result;
        }

        return FALSE;
    }

    /*
    * Returns array containg attribute mappings between Moodle and Identity Provider.
    */
    function get_attributes() {
        return array();
    }

    /**
    * Returns true if this authentication plugin is 'internal'.
    *
    * @return bool
    */
    function is_internal() {
        return false;
    }

    /**
    * Returns true if this authentication plugin can change the user's
    * password.
    *
    * @return bool
    */
    function can_change_password() {
        return false;
    }

    function loginpage_hook() {
        // Prevent username from being shown on login page after logout
        $GLOBALS['CFG']->nolastloggedin = true;

        return;
    }

    function logoutpage_hook() {
        unset($SESSION->SAMLSessionControlled);
        if($this->config->dologout) {
            set_moodle_cookie('nobody');
            require_logout();
            redirect($GLOBALS['CFG']->wwwroot.'/auth/igovt/index.php?logout=1');
        }
    }

    /**
    * Prints a form for configuring this authentication plugin.
    *
    * This function is called from admin/auth.php, and outputs a full page with
    * a form for configuring this plugin.
    *
    * @param array $page An object containing all the data for this page.
    */

    function config_form($config, $err, $user_fields) {
        include "config.html";
    }

    /**
     * A chance to validate form data, and last chance to
     * do stuff before it is inserted in config_plugin
     */
     function validate_form(&$form, &$err) {
        global $CFG;
        $currentdir = getcwd();
        chdir($CFG->dirroot . '/auth/igovt/');
        require_once('config.php');
        if (empty($SIMPLESAMLPHP_LIB) || !file_exists($SIMPLESAMLPHP_LIB.'/lib/_autoload.php')) {
            $err['samllib'] = get_string('errorbadlib', 'auth_igovt', $SIMPLESAMLPHP_LIB);
        }
        if (!isset ($SIMPLESAMLPHP_CONFIG) || !file_exists($SIMPLESAMLPHP_CONFIG.'/config.php')) {
            $err['samlconfig'] = get_string('errorbadconfig', 'auth_igovt', $SIMPLESAMLPHP_CONFIG);
        }
        chdir($currentdir);
     }

    /**
    * Processes and stores configuration data for this authentication plugin.
    *
    *
    * @param object $config Configuration object
    */
    function process_config($config) {
        // set to defaults if undefined
        if (!isset ($config->dologout)) {
            $config->dologout = '';
        }
        if (!isset ($config->newflt)) {
            $config->newflt = 'createuser';
        }
        if (!isset ($config->duallogin)) {
            $config->duallogin = '';
        }
        if (!isset ($config->autoinvite)) {
            $config->autoinvite = '';
        }
        if (!isset ($config->inviteexpirynotify)) {
            $config->inviteexpirynotify = '';
        }
        if (!isset ($config->inviteexpiry)) {
            $config->inviteexpiry = 0;
        }
        if (!isset ($config->invitemaxtries)) {
            $config->invitemaxtries = 0;
        }
        if (!isset ($config->logonfailurenotify)) {
            $config->logonfailurenotify = '';
        }

       // save settings
        set_config('dologout',              $config->dologout,              'auth/igovt');
        set_config('newflt',                $config->newflt,                'auth/igovt');
        set_config('duallogin',             $config->duallogin,             'auth/igovt');
        set_config('autoinvite',            $config->autoinvite,            'auth/igovt');
        set_config('inviteexpiry',          $config->inviteexpiry,          'auth/igovt');
        set_config('inviteexpirynotify',    $config->inviteexpirynotify,    'auth/igovt');
        set_config('invitemaxtries',        $config->invitemaxtries,        'auth/igovt');
        set_config('logonfailurenotify',    $config->logonfailurenotify,    'auth/igovt');

        return true;
    }


    function cron() {
        global $CFG;

        if ($this->config->autoinvite) {
            $this->log('Checking for users to invite');
            $this->inviteusers();
        }

        if (!empty($this->config->inviteexpiry)) {
            $expdate = strtotime('midnight -'.($this->config->inviteexpiry + 1).' days');

            if (!empty($this->config->inviteexpirynotify)) {
                $sql = "SELECT u.id, u.firstname, u.lastname, u.email, u.idnumber
                        FROM {$CFG->prefix}user u
                            INNER JOIN {$CFG->prefix}auth_igovt_invitations i ON i.userid = u.id
                        WHERE status = 'invited' AND datecreated < $expdate AND u.deleted = 0
                        ORDER BY firstname, lastname ";

                if($expired = get_records_sql($sql)) {
                    $this->notify_expired($expired);
                }

            }
            // Expiry old invitations
            $sql = "UPDATE {$CFG->prefix}auth_igovt_invitations
                    SET status = 'expired', datechanged = ".time()."
                    WHERE status = 'invited' AND datecreated < $expdate ";

            $this->log("Expiring old access codes");
            execute_sql($sql);
        }
    }

    function inviteusers() {
        global $CFG;

        $sql = "SELECT u.*
                FROM {$CFG->prefix}user u
                LEFT JOIN {$CFG->prefix}auth_igovt_invitations i ON u.id = i.userid
                WHERE i.id IS NULL AND u.auth = 'igovt' AND u.deleted = 0";
        $users = get_recordset_sql($sql);

        if ($users) {
            $this->log('Sending igovt invitations to '.$users->recordCount().' users');
            foreach ($users as $user) {
               $user = (object)$user;
               $this->inviteuser($user);
               $this->log("\t".fullname($user, true).' <'.$user->email.">\n");
            }
        }

    }

    function inviteuser($user) {
        global $CFG;

        $accesscode = generate_password();

        $accesscodehash = sha1($accesscode);

        $site  = get_site();

        $supportuser = generate_email_supportuser();

        $a = new object();
        $a->firstname   = fullname($user, true);
        $a->sitename    = format_string($site->fullname);
        $a->accesscode  = $accesscode;
        $a->link        = preg_replace('#http(s)?://#', '', $CFG->wwwroot);
        $a->signoff     = generate_email_signoff();
        $a->imageroot   = $CFG->wwwroot.'/auth/igovt/images/';
        $a->email       = $user->email;

        if ($this->config->inviteexpiry > 0) {
            $a->invitationexpiry = get_string('invitation_expiry', 'auth_igovt', userdate(time() + $this->config->inviteexpiry * 86399,  '%d-%h-%y'));
        }
        else {
            $a->invitationexpiry = '';
        }

        $message = get_string('invitation_email', 'auth_igovt', $a);
        $messagehtml = get_string('invitation_emailhtml', 'auth_igovt', $a);
        $subject = get_string('invitation_subject', 'auth_igovt', $a);

        $inv = new object();
        $inv->userid = $user->id;
        $inv->accesscodehash = $accesscodehash;
        $inv->datecreated = time();
        $inv->status = 'invited';
        $inv->datechanged = time();

        if (insert_record('auth_igovt_invitations', $inv)) {
            return email_to_user($user, $supportuser, $subject, $message, $messagehtml);
        } else {
            return false;
        }

    }


    function log($msg) {
        $this->log .= "[auth/igovt]: $msg\n";
    }


    function notify_expired($expired) {
        $list = '';

        $from = generate_email_supportuser();

        $sendto = new object;
        $sendto->email = $this->config->inviteexpirynotify;
        $sendto->firstname = '';
        $sendto->lastname = '';
        $sendto->maildisplay = true;


        foreach($expired as $user) {
            $list .= "\t".fullname($user)." <{$user->email}>\n";
        }
        email_to_user($sendto, $from, get_string('expirynotificationsubject', 'auth_igovt'), get_string('expirynotificationbody', 'auth_igovt', $list));
    }

    function notify_logonfailure($condition, $user) {

        if (empty($this->config->logonfailurenotify)) {
            return false;
        }

        $from = generate_email_supportuser();

        $sendto = new object;
        $sendto->email = $this->config->logonfailurenotify;
        $sendto->firstname = '';
        $sendto->lastname = '';
        $sendto->maildisplay = true;

        switch ($condition)  {
            case 'expired':
                email_to_user($sendto, $from, get_string('notifyexpiredsubject', 'auth_igovt'), get_string('notifyexpiredbody', 'auth_igovt', fullname($user)));
                break;
            case 'locked':
                email_to_user($sendto, $from, get_string('notifylockedsubject', 'auth_igovt'), get_string('notifylockedbody', 'auth_igovt', fullname($user)));
                break;
            case 'cancelled':
                email_to_user($sendto, $from, get_string('notifycancelledsubject', 'auth_igovt'), get_string('notifycancelledbody', 'auth_igovt', fullname($user)));
                break;
        }
    }

}

?>
