<?php
global $CFG;

$string['auth_igovttitle']         = 'igovt Authentication';
$string['auth_igovtdescription']   = 'Logon plugin for the NZ igovt logon service.<br/><br/> Do not forget to edit the configuration file: '.$CFG->dirroot.'/auth/igovt/config.php';

$string['dologout'] = 'Log out from Identity Provider';
$string['dologout_description'] = 'Check to have the module log out from Identity Provider when user log out from Moodle';

$string['newflt'] = 'On first logon attempt';
$string['newflt_description'] = '';
$string['requireaccesscode'] = 'Require access code';
$string['createuser'] = 'Automatically create user';

$string['duallogin'] = 'Enable Dual login for users';
$string['duallogin_description'] = 'Enable use of users assigned login auth module and igovt';

$string['autoinvite'] = 'Automatically invite users to login with igovt';
$string['autoinvite_description'] = 'New accounts with an auth type of igovt will automatically be emailed an invitation and login code. <br /><br /><strong>NOTE: enabling this setting will result in $a->count people being emailed</strong>';


$string['inviteexpiry'] = 'Invitation expiry (days)';
$string['inviteexpiry_description'] = 'Access codes not used within the specified number of days will be disabled. Users will need to re-request access once their access code has expired. 0 = never expire.';
$string['invitemaxtries'] = 'Maximum logon attemps';
$string['invitemaxtries_description'] = 'Maximum number of times a user can fail to enter access code before their access code is locked. 0 = never lock code.';


$string['errorbadlib'] = 'SimpleSAMLPHP lib directory $a is not correct.  Please edit the auth/igovt/config.php file correctly.';
$string['errorbadconfig'] = 'SimpleSAMLPHP config directory $a is in correct.  Please edit the auth/igovt/config.php file correctly.';

$string['retriesexceeded'] = 'Maximum number of retries exceeded ($a) - there must be a problem with the Identity Service';
$string['pluginauthfailed'] = 'This user has been disabled and cannot be logged on. For further assistance, please email your Moodle administrator';
$string['auth_igovt_username_error'] = 'IdP returned a set of data that does not contain the SAML username mapping field. This field is required to login';

$string['auth_igovt_error_authfailure'] = 'You have chosen to leave igovt<br /><br /><a href=\"logon.php\">Return to igovt logon</a>';
$string['auth_igovt_error_noidp'] = 'Auth fail';
$string['auth_igovt_error_internal'] = 'igovt was unable to process your request due to an igovt internal error. Please try again. If the problem persists, please contact igovt Help Desk on 0800 457 4357';
$string['auth_igovt_error_timeout'] = 'Your igovt session has timed out please try again<br /><br /><a href=\"logon.php\">Return to igovt logon</a>';
$string['auth_igovt_error_generic'] = 'igovt reported a serious application error with the message [$a]. Please try again later. If the problem persists, please contact igovt Help Desk on 0800 457 4357';
$string['invitation_subject'] = 'Moodle invitation';
$string['invitation_email'] = 
'You have been invited to logon to $a->link

Follow these steps to log on to Moodle:

    1. Log on via igovt
    2. Have your access code handy (see below) and enter it when
       requested. This is a one-time process.


igovt logon
================

    Please enter $a->link in your browser
    to reach the Moodle start page.

    Select the igovt \"Log on\" button.

    This link will take you to the igovt logon service where you should
    log on if you already have an igovt username and password, or create
    an igovt logon by following the \"New User\" link.



Access code
================

    MOodle will prompt you to enter the access code shown below to
    complete your access.

    Your invitation email address is: $a->email
    Your access code which can\'t be used by anyone else is: $a->accesscode

$a->invitationexpiry

';

$string['invitation_emailhtml'] = '<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
    <html xmlns=\"http://www.w3.org/1999/xhtml\">
        <head>
            <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />
        </head>
        <body style=\"font-family: Arial, Helvetica, sans-serif; font-size: 12px; line-height: 18px;\">
                    <p>You have been invited to use $a->link</p>
                    <p><strong>Follow these steps to log on to Moodle:</strong></p>
                    <ol>
                        <li>Log on via igovt</li>
                        <li>Have your access code handy (see below) and enter it when requested.<br /> This is a one-time process.</li>
                    </ol>
                    <h3>igovt logon</h3>
                    <p>
                       Please enter $a->link in your browser to reach the Moodle start page.<br />
                       Select the igovt &quot;Log on&quot; button.<br />
                       This link will take you to the igovt logon service where you can log on if you already have an igovt username and password, or create an igovt logon.
                    </p>
                    <h3>Access code</h3>
                    <p>Moodle will prompt you to enter the access code shown below to confirm your access.</p>
                    <p>Your invitation email address is: <strong>$a->email</strong>
                    <p>Your access code which can\'t be used by anyone else is: <strong><code>$a->accesscode</code></strong></p>
                    <p>$a->invitationexpiry</p>
       </body>
    </html>
';

$string['diainvitetext'] = '';
$string['invitation_expiry'] =
'    Your invitation will expire at midnight on $a. You
    will need to use your access code before that date. You can request a
    new invitation from the email address below.';

$string['logonabout'] = 'What is igovt?';
$string['logonaboutalt'] = 'This link will take you to the igovt logon service website to access more information.';
$string['logonalt'] = 'This link will take you to the igovt logon service where you can log on, or create a logon if you don&#39;t already have one.';

$string['lsllogonheading'] = 'Moodle access confirmation';
$string['lsllogondesc'] = 'Enter the email address used by your invitation email, and the access code provided.<br />This step is a one-time process; you will not be required to do this again.';
$string['accesscode'] = 'Access code';
$string['validatecode'] = 'Validate Access Code';
$string['emailoraccessnotfound'] = 'Email or access code incorrect';
$string['accesscodeexpired'] = 'The access code you have entered has expired';
$string['accesscodelocked'] = 'You have exceeded the allowed number of attempts to use an access code.<br />Conact your Learning administrator to obtain a new access code';
$string['accesscodeused'] = 'Access code already validated';
$string['accesscodecancelled'] = 'Access code has been cancelled';

$string['requestinvitelong'] = 'Request an igovt logon invitation and access code';
$string['requestinvite'] = 'Request an access code';
$string['needaccesscode'] = 'Access code expired or do not have an access code?';
$string['requestinvitedesc'] = 'If you have lost your invitation, it has expired or been locked due to repeated logon failures then you can request a new invitation email by entering your email address below';
$string['requestinvitepost'] = 'A new invitation and access code will be emailed to you.';
$string['requestsent'] = 'Your new access code has been sent to the email address recorded with Moodle.';
$string['backtolsllogon'] = 'Back to logon confirmation';
$string['backtologon'] = 'Back to logon';

$string['igovtstatus'] = 'igovt status';
$string['igovtstatusis'] = 'igovt status is \'$a->status\'';
$string['invited'] = 'Invited';
$string['cancelled'] = 'Cancelled';
$string['locked'] = 'Locked';
$string['expired'] = 'Expired';
$string['validated'] = 'Active';
$string['notinvited'] = 'Not invited';
$string['datelabelisbetween'] = 'and igovt status changed between $a->before and $a->after';
$string['datelabelisafter'] = 'and igovt status changed after $a->after';
$string['datelabelisbefore'] = 'and igovt status changed before $a->before';

$string['igovtinvite'] = 'Send igovt invitation';
$string['igovtcancel'] = 'Cancel igovt invitation';
$string['igovtinvitecheckfull'] = 'Are you sure you would like to email igovt invitations to $a';
$string['igovtinvitationssent'] = 'igovt invitations sent';
$string['igovtcancelcheckfull'] = 'Are you sure you would like to cancel igovt invitations for $a <br /><br />(NOTE: accepted invitations will not be cancelled)';
$string['igovtinvitationscancelled'] = 'igovt invitations cancelled';
$string['useralreadyinvited'] = '$a has already been invited';

$string['inviteexpirynotify'] = 'Send expiry notifications to';
$string['inviteexpirynotify_description'] = 'Send a daily digest of expired invitations to the specified email address. If left blank no digests will be sent.';
$string['expirynotificationsubject'] = 'Expired igovt invitations';
$string['expirynotificationbody'] = 'The following users invitations and access codes have expired:\n\n $a\n\n';


$string['logonfailurenotify'] = 'Notify email for logon failures';
$string['logonfailurenotify_description'] = 'Send notification of exceeded logon attepts, expired logon attempts and cancelled logon attemts. If left blank no email\'s will be sent.';

$string['notifylockedsubject'] = 'igovt logon attemps exceeded';
$string['notifylockedbody'] = '$a has exceeded the allowed number of attempts to enter correct email or access code.';

$string['notifyexpiredsubject'] = 'igovt expired access code';
$string['notifyexpiredbody'] = '$a has attempted to use an expired access code.';

$string['notifycancelledsubject'] = 'igovt suspended account';
$string['notifycancelledbody'] = '$a has attempted to access a suspended Totara account.';

$string['logonintro'] = '<h3>Moodle start page</h3>
<p>Moodle is an online training service
which uses the igovt logon service to check you are entitled to access the site.<br /></br />
</p>';

$string['logonfirstheading'] = 'First time Moodle user?';
$string['logonfirststep1'] = 'Use your igovt logon if you already have one or if you are new to igovt, create one now.';
$string['logonfirststep2'] = ' <p>Be ready to use the access code from your invitation email.</p>
                        <p>This step is a one-time process. You will not be asked for your access code again.</p>';

$string['logonreturnheading'] = 'Returning Moodle user?';
$string['logonreturnstep1'] = 'Log on to Moodle using the igovt logon service';

$string['logonhelp'] = '<p>
For further assistance, please email your Moodle administrator
</p>';

$string['igovt'] = 'igovt';
$string['igovtblock'] = 'Manage igovt';
$string['about'] = 'About igovt';
$string['managealt'] = 'This link will take you to the igovt logon service website where you can update and view your details.';
$string['aboutalt'] = 'This link will take you to the igovt logon service website to access more information.';

?>
