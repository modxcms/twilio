<?php
/**
 * Creates a new TOTP factor for the current user.
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$twilio = new MODX\Twilio\Twilio($modx, $scriptProperties);

return (new \MODX\Twilio\Snippet\TotpCreate($twilio, $scriptProperties))->process();
