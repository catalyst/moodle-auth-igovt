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

// User is already logged in
if (isloggedin()) {
    redirect($CFG->wwwroot);
    exit;
}

if (!empty($_SESSION['auth_igovt_flt'])) {
    unset($_SESSION['auth_igovt_flt']);
    $auth = get_auth_plugin('igovt');
    $auth->logoutpage_hook();
}

/// Define variables used in page
if (!$site = get_site()) {
    error("No site found!");
}

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

$wantsurl = optional_param('wantsurl', $CFG->wwwroot.'/my/learning.php', PARAM_URL);

print_header("$site->fullname: $logonsite", $site->fullname, $navigation, '',
        '', true, '<div class="langmenu">'.$langmenu.'</div>');


?>
<link rel="stylesheet" type="text/css" href="./css/igovt_buttons.css" />

<div style="margin:50px">
    <?php echo get_string('logonintro', 'auth_igovt'); ?>
    <div class="igovtcol">
        <div class="box">
            <div class="header" >
                <div class="title"><h2><?php echo get_string('logonfirstheading', 'auth_igovt'); ?></h2></div>
            </div>
            <div class="content">
                <ol>
                    <li><?php echo get_string('logonfirststep1', 'auth_igovt'); ?>
                        <div class="igovtPopupButtonContainer">
                            <div class="hasIgovtPopup">
                                <a href="index.php?wantsurl=<?php echo urlencode($wantsurl) ?>">
                                    <img src="./images/log-on-button-white.gif" width="149" height="28" />
                                    <span class="popup showInfoBtnLeftUp">
                                        <span><?php echo $logonalt ?></span>
                                    </span>
                                </a>
                            </div>
                            <div class="hasIgovtPopup onLeftStdBtn">
                                <a href="http://www.i.govt.nz"><?php echo $logonabout ?>
                                    <span class="popup showInfoLinkLeft">
                                        <span><?php echo $logonaboutalt ?></span>
                                    </span>
                                </a>
                            </div>
                        </div>

                    </li>
                    <li>
                        <?php echo get_string('logonfirststep2', 'auth_igovt'); ?>
                    </li>
                </ol>
            </div>
        </div>
    </div>
    <div class="igovtcol" style="float: right">
        <div class="box">
            <div class="header" >
                <div class="title"><h2><?php echo get_string('logonreturnheading', 'auth_igovt'); ?></h2></div>
            </div>
            <div class="content" style="padding:20px 40px">
                <?php echo get_string('logonreturnstep1', 'auth_igovt'); ?>
                <div class="igovtPopupButtonContainer">
                    <div class="hasIgovtPopup">
                        <a href="index.php?wantsurl=<?php echo urlencode($wantsurl) ?>">
                            <img src="./images/log-on-button-white.gif" width="149" height="28" />
                            <span class="popup showInfoBtnLeftUp">
                                <span><?php echo $logonalt ?></span>
                            </span>
                        </a>
                    </div>
                    <div class="hasIgovtPopup onLeftStdBtn">
                        <a href="http://www.i.govt.nz"><?php echo $logonabout ?>
                            <span class="popup showInfoLinkLeft">
                                <span><?php echo $logonaboutalt ?></span>
                            </span>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div style="clear: both; padding: 16px">
        <?php echo get_string('logonhelp', 'auth_igovt'); ?>
    </div>
</div>
<?php
print_footer();
