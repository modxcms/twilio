<?php
/**
 * Validates phone number
 * [[!Register? &customValidators=`TwilioValidatePhone` &validate=`phone:TwilioValidatePhone`]]
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$twilio = new MODX\Twilio\Twilio($modx, $scriptProperties);

return (new \MODX\Twilio\Snippet\ValidatePhone($twilio, $scriptProperties))->process();
