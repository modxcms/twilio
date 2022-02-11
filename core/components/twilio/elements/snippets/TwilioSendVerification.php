<?php
/**
 * Delivers verification code through the selected channel (sms, call, email)
 * This is a FormIt hook
 * [[!FormIt? &hooks=`TwilioSendVerification` &submitVar=`get-validation` &validate=`channel:required` ]]
 *
 * Available options:
 * &twilioServiceId         string      ID of the Twilio service, if empty, hook will use system setting twillio.service
 * &twilioAllowedChannels   string      Comma delimited list of allowed channels. Available options: sms, call, email Default: sms,call
 * &twilioSendLimit         number      Time in minuted for repeated verification code requests. Use won't be able to request another code, before this time. Default: 15
 *
 * Available placeholder:
 * [[!+twilio.code_sent]]   Contains a message if verification code was successfully sent
 *
 * @var modX $modx
 * @var array $scriptProperties
 */
$twilio = $modx->getService('twilio', 'Twilio', $modx->getOption('twilio.core_path', null, $modx->getOption('core_path') . 'components/twilio/') . 'model/twilio/');
if (!($twilio instanceof \Twilio)) return '';

return (new \MODX\Twilio\Snippet\SendVerification($twilio, $scriptProperties))->process();