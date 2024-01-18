<?php

abstract class TwilioBaseManagerController extends modExtraManagerController
{
    public string $version = '1.2.3';
    public function checkPermissions()
    {
        return true;
    }

    public function getLanguageTopics()
    {
        return array('twilio:default', 'user');
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
        $this->modx->lexicon->load('twilio:default');
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
}
