<?php

/**
 * @var $modx
 * @var $scriptProperties array
 */

if (empty($modx->version)) {
    $modx->getVersionData();
}
$version = (int) $modx->version['version'];

if ($version > 2) {
    $twilio = new MODX\Twilio\Twilio($modx, $scriptProperties);
} else {
    $corePath = $modx->getOption('twilio.core_path', null, $modx->getOption('core_path') . 'components/twilio/');
    $twilio = $modx->getService('twilio', 'Twilio', $corePath . 'model/twilio/', $scriptProperties);
}
$className = "\\MODX\\Twilio\\Event\\{$modx->event->name}";


if (class_exists($className)) {
    /** @var \MODX\Twilio\Event\Event $event */
    $event = new $className($twilio, $scriptProperties);
    $event->run();
} else {
    $modx->log(\xPDO::LOG_LEVEL_ERROR, "Class {$className} not found");
}
return;
