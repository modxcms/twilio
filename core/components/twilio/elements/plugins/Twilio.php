<?php

use xPDO\xPDO;

$twilio = new MODX\Twilio\Twilio($modx, $scriptProperties);

$className = "\\MODX\\Twilio\\Event\\{$modx->event->name}";
if (class_exists($className)) {
    /** @var \MODX\Twilio\Event\Event $event */
    $event = new $className($twilio, $scriptProperties);
    $event->run();
} else {
    $modx->log(xPDO::LOG_LEVEL_ERROR, "Class {$className} not found");
}
return;
