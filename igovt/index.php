<?php
/**
 * index.php - landing page for auth/igovt based SAML 2.0 login
 *
 * builds basic CFG and DB connection to Moodle, to then get the saml plugin
 * configuration.
 *
 * Does the SimpleSAMLPHP calls to query SAML 2.0 session status,
 *
 * Builds the rest of Moodle session, and then logs the user in.
 *
 * @originalauthor Martin Dougiamas
 * @author Erlend Strømsvik - Ny Media AS
 * @author Piers Harding - made quite a number of changes
 * @author Matt Clarkson - forked as igovt plugin
 * @version 1.0
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package auth/igovt
 */
global $CFG, $USER, $SESSION;

define('SAML_INTERNAL', 1);
define('SAML_RETRIES', 5);

// Pull in the SimpleSAMLphp Config - you must configure this to point to your
// SimpleSAMLphp install for SP
require_once('config.php');

// now boot strap SimpleSAMLPHP and get everything that we could
// possibly need data wise
require_once($SIMPLESAMLPHP_LIB . '/lib/_autoload.php');
SimpleSAML_Configuration::init($SIMPLESAMLPHP_CONFIG);

// figure out where to send us after the logout
if (empty($SIMPLESAMLPHP_LOGOUT_LINK)) {
    if (preg_match('/^(.*?)auth\/igovt.*?$/', auth_igovt_qualified_me() . auth_igovt_me(), $matches)) {
        $SIMPLESAMLPHP_LOGOUT_LINK = $matches[1];
    }
    else {
        $SIMPLESAMLPHP_LOGOUT_LINK = auth_igovt_qualified_me();
    }
}

// grab the ssphp instance information
$saml_config = SimpleSAML_Configuration::getInstance();
$saml_session = SimpleSAML_Session::getInstance();
$as = new SimpleSAML_Auth_Simple($SIMPLESAMLPHP_SP);
$valid_saml_session = $saml_session->isValid($SIMPLESAMLPHP_SP);

// either way we don't want to be SAML controlled at this stage
unset($SESSION->SAMLSessionControlled);

// check what kind of request this is
if(isset($_GET["logout"])) { // only existence check this param
    if (isset($SESSION->retry)) {
        $SESSION->retry = 0;
    }
    if($valid_saml_session) {
        $as->logout($SIMPLESAMLPHP_LOGOUT_LINK);
    } else {
        @session_write_close();
        @header($_SERVER['SERVER_PROTOCOL'] . ' 303 See Other');
        @header('Location: '.$SIMPLESAMLPHP_LOGOUT_LINK);
        die;
    }
    // should never get here
    exit(0);
}

/**
 * check that the saml session is OK - if not send to the IdP for authentication
 * if good, then do the Moodle login, and send to the home page, or landing page
 * if otherwise specified
 */
$retry = isset($SESSION->retry) ? $SESSION->retry : 0;
if ($retry == SAML_RETRIES) {
    // too many tries at logging in
    session_write_close();
    require_once('../../config.php');
    print_error('retriesexceeded', 'auth_igovt', '', $retry);
}
$SESSION->retry = $retry + 1;

// save the jump target - this is checked later that it
// starts with $CFG->wwwroot, and cleaned
if (isset($_GET['wantsurl'])) {
    $SESSION->wantsurl = $_GET['wantsurl'];
}

if (isset($_GET['error'])) {
   header('Location: error.php?error='.urlencode($_GET['error']).'&message='.urlencode($_GET['message']));
   exit;
}

// now - are we logged in?
$as->requireAuth(
    array('ReturnTo'           => auth_igovt_qualified_me().'/auth/igovt/',
           'saml:NameIDPolicy' => array('Format' => SAML2_Const::NAMEID_PERSISTENT,
                                        'AllowCreate' => TRUE))
    );

// get the SAML user attributes
$saml_attributes = $as->getAttributes();
// if we get here, then everything is OK - shutdown the ssphp
// side of things, and continue with Moodle
$wantsurl = isset($SESSION->wantsurl) ? $SESSION->wantsurl : FALSE;
unset($SESSION->retry);
unset($SESSION->wantsurl);
session_write_close();

// do the normal Moodle bootstraping so we have access to all config and the DB
require_once('../../config.php');

// check for a wantsurl in the existing Moodle session
if (empty($wantsurl) && isset($SESSION->wantsurl)) {
    $wantsurl = $SESSION->wantsurl;
}

// get the plugin config for saml
$pluginconfig = get_config('auth/igovt');

// Valid session. Register or update user in Moodle, log him on, and redirect to Moodle front

// we require the plugin to know that we are now doing a saml login in hook puser_login
$GLOBALS['saml_login'] = TRUE;

// make variables accessible to saml->get_userinfo. Information will be requested from authenticate_user_login -> create_user_record / update_user_record
$GLOBALS['saml_login_attributes'] = $saml_attributes;

// check user name attribute actually passed
if(!isset($saml_attributes['nameid'])) {
    print_error(get_string("auth_igovt_username_error", "auth_igovt"));
}

// just passes time as a password. User will never log in directly to moodle with this password anyway or so we hope?
$username = auth_igovt_addsingleslashes(moodle_strtolower($saml_attributes['nameid'][0]));

$_SESSION['auth_igovt_flt'] = $username;

// check if users are allowed to be created and if the user exists
if (isset($pluginconfig->newflt)) {
    if ($pluginconfig->newflt == 'requireaccesscode' && !get_record('user', 'username', $username)) {
        $USER = new object();
        $USER->id = 0;
        redirect('lsllogon.php');
        exit;
   }
}

if (isset($pluginconfig->duallogin) && $pluginconfig->duallogin) {
    $USER = auth_igovt_authenticate_user_login($username, time());
}
else {
    $USER = authenticate_user_login($username, time());
}

// check that the signin worked
if ($USER == false) {
    // too many tries at logging in
    session_write_close();
    $USER = new object();
    $USER->id = 0;
    require_once('../../config.php');
    print_error('pluginauthfailed', 'auth_igovt', $CFG->wwwroot, $saml_attributes['nameid'][0]);
}

$USER->loggedin = true;
$USER->site     = $CFG->wwwroot;

// complete the user login sequence
$USER = get_complete_user_data('id', $USER->id);
complete_user_login($USER);

if (isset($wantsurl) and (strpos($wantsurl, $CFG->wwwroot) === 0) and $wantsurl != $CFG->wwwroot) {
    $urltogo = clean_param($wantsurl, PARAM_URL);
} else {
    $urltogo = $CFG->wwwroot.'/my/learning.php';
}

//auth_igovt_err($urltogo);

// flag this as a SAML based login
$SESSION->SAMLSessionControlled = true;
redirect($urltogo);


/**
 * Copied from moodlelib:authenticate_user_login()
 *
 * WHY? because I need to hard code the plugins to auth_igovt, and this user
 * may be set to any number of other types of login method
 *
 * First of all - make sure that they aren't nologin - we don't mess with that!
 *
 *
 * Given a username and password, this function looks them
 * up using the currently selected authentication mechanism,
 * and if the authentication is successful, it returns a
 * valid $user object from the 'user' table.
 *
 * Uses auth_ functions from the currently active auth module
 *
 * After authenticate_user_login() returns success, you will need to
 * log that the user has logged in, and call complete_user_login() to set
 * the session up.
 *
 * @uses $CFG
 * @param string $username  User's username (with system magic quotes)
 * @param string $password  User's password (with system magic quotes)
 * @return user|flase A {@link $USER} object or false if error
 */
function auth_igovt_authenticate_user_login($username, $password) {

    global $CFG;

    // ensure that only saml auth module is chosen
    $authsenabled = get_enabled_auth_plugins();

    if ($user = get_complete_user_data('username', $username)) {
        $auth = empty($user->auth) ? 'manual' : $user->auth;  // use manual if auth not set
        if ($auth=='nologin' or !is_enabled_auth($auth)) {
            add_to_log(0, 'login', 'error', 'index.php', $username);
            error_log('[client '.getremoteaddr()."]  $CFG->wwwroot  Disabled Login:  $username  ".$_SERVER['HTTP_USER_AGENT']);
            return false;
        }
//        $auths = array($auth);

    } else {
        // check if there's a deleted record (cheaply)
        if (get_field('user', 'id', 'username', $username, 'deleted', 1, '')) {
            error_log('[client '.$_SERVER['REMOTE_ADDR']."]  $CFG->wwwroot  Deleted Login:  $username  ".$_SERVER['HTTP_USER_AGENT']);
            return false;
        }

        $auths = $authsenabled;
        $user = new object();
        $user->id = 0;     // User does not exist
    }

    // hard code only saml module
    $auths = array('igovt');
    foreach ($auths as $auth) {
        $authplugin = get_auth_plugin($auth);

        // on auth fail fall through to the next plugin
        if (!$authplugin->user_login($username, $password)) {
            continue;
        }

        // successful authentication
        if ($user->id) {                          // User already exists in database
            if (empty($user->auth)) {             // For some reason auth isn't set yet
                set_field('user', 'auth', $auth, 'username', $username);
                $user->auth = $auth;
            }
            if (empty($user->firstaccess)) { //prevent firstaccess from remaining 0 for manual account that never required confirmation
                set_field('user','firstaccess', $user->timemodified, 'id', $user->id);
                $user->firstaccess = $user->timemodified;
            }

            // we don't want to upset the existing authentication schema for the user
//            update_internal_user_password($user, $password); // just in case salt or encoding were changed (magic quotes too one day)

            if (!$authplugin->is_internal()) {            // update user record from external DB
                $user = update_user_record($username, get_auth_plugin($user->auth));
            }
        } else {
            // if user not found, create him
            $user = create_user_record($username, $password, $auth);
        }

        $authplugin->sync_roles($user);

        foreach ($authsenabled as $hau) {
            $hauth = get_auth_plugin($hau);
            $hauth->user_authenticated_hook($user, $username, $password);
        }

        if ($user->id===0) {
            return false;
        }
        return $user;
    }

    // failed if all the plugins have failed
    add_to_log(0, 'login', 'error', 'index.php', $username);
    if (debugging('', DEBUG_ALL)) {
        error_log('[client '.getremoteaddr()."]  $CFG->wwwroot  Failed Login:  $username  ".$_SERVER['HTTP_USER_AGENT']);
    }
    return false;
}

/**
 * Add slashes for single quotes and backslashes
 * so they can be included in single quoted string
 * (for config.php)
 */
function auth_igovt_addsingleslashes($input){
    return preg_replace("/(['\\\])/", "\\\\$1", $input);
}


// useful functions copied from Moodle lib/weblib.php - why? need to be able to
// run these without having bootstrapped Moodle

/**
 * Like {@link me()} but returns a full URL
 * @see me()
 * @return string
 */
function auth_igovt_qualified_me() {

    global $CFG;

    if (!empty($CFG->wwwroot)) {
        $url = parse_url($CFG->wwwroot);
    }

    if (!empty($url['host'])) {
        $hostname = $url['host'];
    } else if (!empty($_SERVER['SERVER_NAME'])) {
        $hostname = $_SERVER['SERVER_NAME'];
    } else if (!empty($_ENV['SERVER_NAME'])) {
        $hostname = $_ENV['SERVER_NAME'];
    } else if (!empty($_SERVER['HTTP_HOST'])) {
        $hostname = $_SERVER['HTTP_HOST'];
    } else if (!empty($_ENV['HTTP_HOST'])) {
        $hostname = $_ENV['HTTP_HOST'];
    } else {
        notify('Warning: could not find the name of this server!');
        return false;
    }

    if (!empty($url['port'])) {
        $hostname .= ':'.$url['port'];
    } else if (!empty($_SERVER['SERVER_PORT'])) {
        if ($_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 443) {
            $hostname .= ':'.$_SERVER['SERVER_PORT'];
        }
    }

    // TODO, this does not work in the situation described in MDL-11061, but
    // I don't know how to fix it. Possibly believe $CFG->wwwroot ahead of what
    // the server reports.
    if (isset($_SERVER['HTTPS'])) {
        $protocol = ($_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
    } else if (isset($_SERVER['SERVER_PORT'])) { # Apache2 does not export $_SERVER['HTTPS']
        $protocol = ($_SERVER['SERVER_PORT'] == '443') ? 'https://' : 'http://';
    } else {
        $protocol = 'http://';
    }

    $url_prefix = $protocol.$hostname;
    return $url_prefix;
}

/**
 * Returns the name of the current script, WITH the querystring portion.
 * this function is necessary because PHP_SELF and REQUEST_URI and SCRIPT_NAME
 * return different things depending on a lot of things like your OS, Web
 * server, and the way PHP is compiled (ie. as a CGI, module, ISAPI, etc.)
 * <b>NOTE:</b> This function returns false if the global variables needed are not set.
 *
 * @return string
 */
 function auth_igovt_me() {

    if (!empty($_SERVER['REQUEST_URI'])) {
        return $_SERVER['REQUEST_URI'];

    } else if (!empty($_SERVER['PHP_SELF'])) {
        if (!empty($_SERVER['QUERY_STRING'])) {
            return $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'];
        }
        return $_SERVER['PHP_SELF'];

    } else if (!empty($_SERVER['SCRIPT_NAME'])) {
        if (!empty($_SERVER['QUERY_STRING'])) {
            return $_SERVER['SCRIPT_NAME'] .'?'. $_SERVER['QUERY_STRING'];
        }
        return $_SERVER['SCRIPT_NAME'];

    } else if (!empty($_SERVER['URL'])) {     // May help IIS (not well tested)
        if (!empty($_SERVER['QUERY_STRING'])) {
            return $_SERVER['URL'] .'?'. $_SERVER['QUERY_STRING'];
        }
        return $_SERVER['URL'];

    } else {
        notify('Warning: Could not find any of these web server variables: $REQUEST_URI, $PHP_SELF, $SCRIPT_NAME or $URL');
        return false;
    }
}


function auth_igovt_err($msg) {
    $stderr = fopen('php://stderr', 'w');
    fwrite($stderr,"auth_plugin_igovt: ". $msg . "\n");
    fclose($stderr);
}

?>
