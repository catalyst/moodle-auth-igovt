<?php

require('../../config.php');

$error = required_param('error', PARAM_TEXT);
$message = optional_param('message', '', PARAM_TEXT);

$errorstrings = array(
    'sspmod_saml_Error: Responder/AuthnFailed' => 'error_authfailure',
    'sspmod_saml_Error: Responder/NoAvailableIDP' => 'error_generic',
    'SimpleSAML_Error_NoPassive' => 'error_generic',
    'sspmod_saml_Error: Responder/RequestUnsupported' => 'error_generic',
    'sspmod_saml_Error: Responder/urn:nzl:govt:ict:stds:authn:deployment:GLS:SAML:2.0:status:InternalError' => 'error_internal',
    'sspmod_saml_Error: Responder/urn:nzl:govt:ict:stds:authn:deployment:GLS:SAML:2.0:status:Timeout' => 'error_timeout'
    );

if (isset($errorstrings[$error])) {
    $errorstring = get_string('auth_igovt_'.$errorstrings[$error], 'auth_igovt', $error);
} else {
    $errorstring = get_string('auth_igovt_error_generic', 'auth_igovt', $error);
}

error($errorstring);
?>
