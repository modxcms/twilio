<?php
$twilio = $modx->getService('twilio', 'Twilio', $modx->getOption('twilio.core_path', null, $modx->getOption('core_path') . 'components/twilio/') . 'model/twilio/');
if (!($twilio instanceof \Twilio)) return '';

$className = "\\MODX\\Twilio\\Event\\{$modx->event->name}";
if (class_exists($className)) {
    /** @var \MODX\Twilio\Event\Event $event */
    $event = new $className($twilio, $scriptProperties);
    $event->run();
} else {
    $modx->log(\xPDO::LOG_LEVEL_ERROR, "Class {$className} not found");
}
return;
