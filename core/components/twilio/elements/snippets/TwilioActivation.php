<?php
/**
 * Post hook for Register snippet: [[!Register? &postHooks=`TwilioActivation` &moderate=`1`]]
 * Use together with &moderate=`1` to register user in deactivated state. This snippet handles sending user to the verification resource + sending an optional email with link to the verification resource.
 *
 * Available options:
 * &twilioRedirectToActivationResource      1/0     If set to 1, will redirect user to the twilioActivationResourceId after registering. Default: 1
 * &twilioActivationResourceId              number  ID of the resource, where user gets redirected after registering. Default: 1
 * &twilioActivationTTL                     number  Time in minutes for the activation resource to be available. If user open the activation page after this time passes, he'll end up on error page. Default: 180
 * &twilioActivationEmailTpl                string  Name of the chunk to use as email template.
 * &twilioActivationEmailSubject            string  Subject of the activation email. Default: Activate your account
 * &twilioEmailFrom                         string  Email address used as from field of activation email. Default: system setting - emailsender
 * &twilioEmailFromName                     string  Name of the sender of the activation email. Default: system setting - site_name
 * &twilioEmailSender                       string  Email used as reply-to. Default: system setting - emailsender
 *
 *
 * @var modX $modx
 * @var array $scriptProperties
 */

$twilio = new MODX\Twilio\Twilio($modx, $scriptProperties);

return (new \MODX\Twilio\Snippet\Activation($twilio, $scriptProperties))->process();
