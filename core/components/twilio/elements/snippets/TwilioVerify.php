<?php
/**
 * Verifies the verification code against Twilio
 * This is a FormIt hook
 * [[!FormIt? &hooks=`TwilioVerify` &twilioRedirect=`4` &placeholderPrefix=`fiv.` &submitVar=`verify` &validate=`code:required` ]]
 *
 * Available options:
 * &twilioServiceId             string      ID of the Twilio service, if empty, hook will use system setting twillio.service
 * &twilioAutoLogIn             number      If set to 1, user will be automatically logged in after completing the verification. Default: 1
 * &twilioAuthenticateContexts  string      Comma delimited list of context to log in user to after verification. Default: current context
 * &twilioRedirect              number      Resource ID where to redirect user after success verification. Default: empty
 * &twilioFactorType            string      The verification factor type (phone, totp) Default: phone
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

if (empty($modx->version)) {
    $modx->getVersionData();
}
$version = (int) $modx->version['version'];

if ($version > 2) {
    $twilio = new MODX\Twilio\Twilio($modx, $scriptProperties);
    $className = "\\MODX\\Twilio\\Snippet\\Verify";
} else {
    $corePath = $modx->getOption('twilio.core_path', null, $modx->getOption('core_path') . 'components/twilio/');
    $twilio = $modx->getService('twilio', 'Twilio', $corePath . 'model/twilio/', $scriptProperties);
    $className = "\\MODX\\Twilio\\v2\\Snippet\\Verify";
}


if (class_exists($className)) {
    /** @var \MODX\Twilio\Snippet\Snippet $event */
    $event = new $className($twilio, $scriptProperties);
    return $event->process();
} else {
    $modx->log(\xPDO::LOG_LEVEL_ERROR, "Class {$className} not found");
}
return;
