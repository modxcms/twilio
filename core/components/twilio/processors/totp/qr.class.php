<?php

class TwilioTotpQrProcessor extends modProcessor
{
    public $languageTopics = array('twilio:default');
    public $objectType = 'twilio.totp';

    public function process()
    {
        $id = $this->getProperty('user');
        $user = $this->modx->getObject('modUser', $id);

        if ($user) {
            $setting = $this->modx->getObject('modUserSetting', array('user' => $user->id, 'key' => 'twilio.totp'));
            if (!$setting || !$setting->get('value')) {
                return $this->failure();
            }
            $profile = $user->getOne('Profile');
            $extended = $profile->get('extended');
            $userTwilio = $extended['twilio_totp'];
            $uri = $userTwilio['binding']['uri'];
            $qr = (new \chillerlan\QRCode\QRCode)->render($uri);

            return $this->success('', ['qr' => $qr]);
        }

        return $this->failure();
    }
}
return 'TwilioTotpQrProcessor';
