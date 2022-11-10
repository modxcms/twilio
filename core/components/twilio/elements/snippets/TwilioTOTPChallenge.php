<?php
/**
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$twilio = new MODX\Twilio\Twilio($modx, $scriptProperties);

return (new \MODX\Twilio\Snippet\TotpChallenge($twilio, $scriptProperties))->process();
