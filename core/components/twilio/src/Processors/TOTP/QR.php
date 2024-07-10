<?php
namespace MODX\Twilio\Processors\TOTP;

use MODX\Revolution\Processors\Processor;
use MODX\Revolution\modUser;
use MODX\Revolution\modUserSetting;

class QR extends Processor
{
    public $languageTopics = array('twilio:default');
    public $objectType = 'twilio.totp';

    public function process()
    {
        $id = $this->getProperty('user');
        $user = $this->modx->getObject(modUser::class, $id);

        if ($user) {
            $setting = $this->modx->getObject(modUserSetting::class, array('user' => $user->id, 'key' => 'twilio.totp'));
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
