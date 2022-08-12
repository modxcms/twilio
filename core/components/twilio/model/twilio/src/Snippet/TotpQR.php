<?php
namespace MODX\Twilio\Snippet;

class TotpQR extends Snippet
{
    public function process()
    {
        $user = $this->modx->user;

        if ($user && $user->id !== 0) {
            $setting = $this->modx->getObject('modUserSetting', array('user' => $user->id, 'key' => 'twilio.totp'));
            if (!$setting || !$setting->get('value')) {
                return;
            }
            $profile = $user->getOne('Profile');
            $extended = $profile->get('extended');
            $userTwilio = $extended['twilio_totp'];
            $uri = $userTwilio['binding']['uri'];
            $qr = 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=' . urlencode($uri);
            $this->modx->setPlaceholder('twilio.qr', $qr);
            $this->modx->setPlaceholder('twilio.secret', $userTwilio['binding']['secret']);
            $this->modx->setPlaceholder('twilio.status', $userTwilio['status']);
        }
    }
}
