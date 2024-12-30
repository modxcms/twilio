<?php

namespace MODX\Twilio\Snippet;

use chillerlan\QRCode\QRCode;
use MODX\Revolution\modUserSetting;

class TotpQR extends Snippet
{
    public function process()
    {
        $user = $this->modx->user;

        if ($user && $user->id !== 0) {
            $setting = $this->modx->getObject(modUserSetting::class, array('user' => $user->id, 'key' => 'twilio.totp'));
            if (!$setting || !$setting->get('value')) {
                return;
            }
            $profile = $user->getOne('Profile');
            $extended = $profile->get('extended');
            $userTwilio = $extended['twilio_totp'];
            $uri = $userTwilio['binding']['uri'];
            $qr = (new QRCode)->render($uri);
            $this->modx->setPlaceholder('twilio.qr', $qr);
            $this->modx->setPlaceholder('twilio.secret', $userTwilio['binding']['secret']);
            $this->modx->setPlaceholder('twilio.status', $userTwilio['status']);
        }
    }
}
