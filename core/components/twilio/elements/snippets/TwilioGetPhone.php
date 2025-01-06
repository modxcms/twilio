<?php
/**
 * Verifies that use can access the verification resource and sets user's phone to placeholder, for the usage in other snippets
 * [[!TwilioGetPhone]]
 *
 * Available options:
 * &phoneField      string      Filed from user profile that is used for storing phone number. Available values: phone, mobilephone. Default: phone
 * &activePage      number      Resource ID where to redirect already activated user. If not set, user will end up on regular error_page. Default empty
 * &errorPage       number      Resource ID to use as an error page
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
    $className = "\\MODX\\Twilio\\Snippet\\GetPhone";
} else {
    $corePath = $modx->getOption('twilio.core_path', null, $modx->getOption('core_path') . 'components/twilio/');
    $twilio = $modx->getService('twilio', 'Twilio', $corePath . 'model/twilio/', $scriptProperties);
    $className = "\\MODX\\Twilio\\v2\\Snippet\\GetPhone";
}


if (class_exists($className)) {
    /** @var \MODX\Twilio\Snippet\Snippet $event */
    $event = new $className($twilio, $scriptProperties);
    return $event->process();
} else {
    $modx->log(\xPDO::LOG_LEVEL_ERROR, "Class {$className} not found");
}
return;
