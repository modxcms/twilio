<?php


$twilio = new MODX\Twilio\Twilio($modx, $scriptProperties);

return (new \MODX\Twilio\Snippet\TotpQR($twilio, $scriptProperties))->process();
