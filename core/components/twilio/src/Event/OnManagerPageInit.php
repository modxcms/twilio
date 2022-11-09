<?php

namespace MODX\Twilio\Event;

class OnManagerPageInit extends Event
{
    public function run()
    {
        $enforceTotp = $this->getOption('twilio.totp_enforce', false);
        $action = $this->getOption('action');
        $user = $this->modx->user;
        if (!$user || $user->id === 0) {
            return false;
        }
        if ($enforceTotp && $action === 'security/profile') {
            $this->modx->regClientStartupScript($this->twilio->getOption('jsUrl') . 'mgr/twilio.js');
            $profile = $user->getOne('Profile');
            $extended = $profile->get('extended');
            $userTwilio = $extended['twilio_totp'];
            $userTwilio['user'] = $user->id;

            $this->twilio->options['user'] = $userTwilio;
            $this->modx->regClientStartupHTMLBlock('<script type="text/javascript">
            Ext.onReady(function() {
                twilio.qrText = "' . $this->modx->lexicon('twilio.qrcode') . '";
                twilio.config = ' . $this->modx->toJSON($this->twilio->options) . ';
                twilio.config.connector_url = "' . $this->twilio->getOption('connectorUrl') . '";
            });
            </script>');
            $this->modx->regClientStartupScript($this->twilio->getOption('jsUrl') . 'mgr/helpers/qr.js');
        }
    }
}
