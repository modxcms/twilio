<?php
/**
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
    $className = "\\MODX\\Twilio\\Snippet\\TotpChallenge";
} else {
    $corePath = $modx->getOption('twilio.core_path', null, $modx->getOption('core_path') . 'components/twilio/');
    $twilio = $modx->getService('twilio', 'Twilio', $corePath . 'model/twilio/', $scriptProperties);
    $className = "\\MODX\\Twilio\\v2\\Snippet\\TotpChallenge";
}


if (class_exists($className)) {
    /** @var \MODX\Twilio\Snippet\Snippet $event */
    $event = new $className($twilio, $scriptProperties);
    return $event->process();
} else {
    $modx->log(\xPDO::LOG_LEVEL_ERROR, "Class {$className} not found");
}
return;

