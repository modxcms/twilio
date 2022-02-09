<?php
/**
 * Verifies the verification code against Twilio
 * This is a FormIt hook
 * [[!FormIt? &hooks=`TwilioVerify` &twilioRedirect=`4` &placeholderPrefix=`fiv.` &submitVar=`verify` &validate=`code:required` ]]
 *
 * Available options:
 * &twilioService               string      ID of the Twilio service, if empty, hook will use system setting twillio.service
 * &twilioAutoLogIn             number      If set to 1, user will be automatically logged in after completing the verification. Default: 1
 * &twilioAuthenticateContexts  string      Comma delimited list of context to log in user to after verification. Default: current context
 * &twilioRedirect              number      Resource ID where to redirect user after success verification. Default: empty
 *
 * @var modX $modx
 * @var array $scriptProperties
 */
$twilio = $modx->getService('twilio', 'Twilio', $modx->getOption('twilio.core_path', null, $modx->getOption('core_path') . 'components/twilio/') . 'model/twilio/');
if (!($twilio instanceof \Twilio)) return '';

return (new \MODX\Twilio\Snippet\Verify($twilio, $scriptProperties))->process();