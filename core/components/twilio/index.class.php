<?php

use MODX\Revolution\modManagerController;

abstract class TwilioBaseManagerController extends modManagerController
{
    public string $version = '2.0.3';

    public function checkPermissions()
    {
        return true;
    }
    public function initialize()
    {
        $corePath = $this->modx->getOption(
            'twilio.core_path',
            null,
            $this->modx->getOption(
                'core_path',
                null,
                MODX_CORE_PATH
            ) . 'components/twilio/'
        );
        $this->twilio = $this->modx->services->get('twilio');
        $this->addJavascript($this->twilio->getOption('jsUrl') . 'mgr/twilio.js');
        $user = $this->modx->user;
        $profile = $user->getOne('Profile');
        $extended = $profile->get('extended');
        if (isset($extended['twilio_totp'])) {
            unset($extended['twilio_top']['binding']);
            $userTwilio = $extended['twilio_totp'];
        } else {
            $userTwilio = [];
        }
        $userTwilio['user'] = $user->id;

        $this->twilio->options['user'] = $userTwilio;
        $this->addHtml('<script type="text/javascript">
        Ext.onReady(function() {
            twilio.config = ' . $this->modx->toJSON($this->twilio->options) . ';
            twilio.config.connector_url = "' . $this->twilio->getOption('connectorUrl') . '";
        });
        </script>');
    }

    public function getLanguageTopics()
    {
        return array('twilio:default', 'user');
    }
}
