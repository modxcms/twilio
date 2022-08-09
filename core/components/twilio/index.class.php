<?php

abstract class TwilioBaseManagerController extends modExtraManagerController
{
    public string $version = '1.0.0';

    public function checkPermissions()
    {
        return $this->modx->hasPermission('twilio_manage_auth');
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
        $this->twilio = $this->modx->getService(
            'twilio',
            'Twilio',
            $corePath . 'model/twilio/',
            array(
                'core_path' => $corePath
            )
        );
        $this->addJavascript($this->twilio->getOption('jsUrl') . 'mgr/twilio.js');
        $user = $this->modx->user;
        $profile = $user->getOne('Profile');
        $extended = $profile->get('extended');
        unset($extended['twilio_top']['binding']);
        $userTwilio = $extended['twilio_totp'];
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
        return array('twilio:default');
    }
}
