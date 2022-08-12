<?php

class TwilioTotpStatusProcessor extends modProcessor
{
    public $languageTopics = array('twilio:default');
    public $objectType = 'twilio.totp';

    public function process()
    {
        $id = $this->getProperty('user');

        $user = $this->modx->getObject('modUser', $id);

        if ($user) {
            $profile = $user->getOne('Profile');
            $extended = $profile->get('extended');
            unset($extended['twilio_totp']);
            $profile->set('extended', $extended);
            if ($profile->save()) {
                return $this->success();
            }
        }
        return $this->failure();
    }
}
return 'TwilioTotpStatusProcessor';
