<?php
/**
 * Delivers verification code through the selected channel (sms, call, email)
 * This is a FormIt hook
 * [[!TwilioGetRegions? &setFirst=`1` &setFirstType=`csv` &tpl=`options` &selected=`[[!+reg.regioncode]]` ]]
 *
 * Available options:
 * &setFirst        string      JSON OR CSV of regions to show first.
 * &setFirstType    string      Type of date expected in setFirst property. Available options: sms, call, email Default: csv
 * &tpl             string      Optional chunk name to create a custom option list. Properties: region, selected (1/0)
 * &selected        number      Optional value to assign as the selected region
 *
 *
 * @var modX $modx
 * @var array $scriptProperties
 */
$twilio = $modx->getService('twilio', 'Twilio', $modx->getOption('twilio.core_path', null, $modx->getOption('core_path') . 'components/twilio/') . 'model/twilio/');
if (!($twilio instanceof \Twilio)) return '';

return (new \MODX\Twilio\Snippet\SupportedRegions($twilio, $scriptProperties))->process();
