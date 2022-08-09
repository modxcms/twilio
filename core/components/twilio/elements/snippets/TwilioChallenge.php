<?php
/**
 *
 * @var modX $modx
 * @var array $scriptProperties
 */
$twilio = $modx->getService('twilio', 'Twilio', $modx->getOption('twilio.core_path', null, $modx->getOption('core_path') . 'components/twilio/') . 'model/twilio/');
if (!($twilio instanceof \Twilio)) return '';

return (new \MODX\Twilio\Snippet\Challenge($twilio, $scriptProperties))->process();
