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

$twilio = new MODX\Twilio\Twilio($modx, $scriptProperties);

return (new \MODX\Twilio\Snippet\GetPhone($twilio, $scriptProperties))->process();
