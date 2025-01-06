<?php
/**
 * Delivers verification code through the selected channel (sms, call, email)
 * This is a FormIt hook
 * [[!FormIt? &hooks=`TwilioSendVerification` &submitVar=`get-validation` &validate=`channel:required` ]]
 *
 * Available options:
 * &twilioServiceId         string      ID of the Twilio service, if empty, hook will use system setting twillio.service
 * &twilioAllowedChannels   string      Comma delimited list of allowed channels. Available options: sms, call, email Default: sms,call
 * &twilioSendLimit         number      Time in minuted for repeated verification code requests. Use won't be able to request another code, before this time. Default: 15
 *
 * Available placeholder:
 * [[!+twilio.code_sent]]   Contains a message if verification code was successfully sent
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
    $className = "\\MODX\\Twilio\\Snippet\\SendVerification";
} else {
    $corePath = $modx->getOption('twilio.core_path', null, $modx->getOption('core_path') . 'components/twilio/');
    $twilio = $modx->getService('twilio', 'Twilio', $corePath . 'model/twilio/', $scriptProperties);
    $className = "\\MODX\\Twilio\\v2\\Snippet\\SendVerification";
}


if (class_exists($className)) {
    /** @var \MODX\Twilio\Snippet\Snippet $event */
    $event = new $className($twilio, $scriptProperties);
    return $event->process();
} else {
    $modx->log(\xPDO::LOG_LEVEL_ERROR, "Class {$className} not found");
}
return;
