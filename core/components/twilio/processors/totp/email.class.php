<?php

class TotpEmailProcessor extends modProcessor
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
            $qr = 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=' . urlencode($uri);
            $lang = $user->getOption('manager_language');
            $this->modx->lexicon->load("$lang:twilio:email");
            $subject = $this->modx->lexicon('twilio.totp.email.subject');
            $body = $this->modx->lexicon('twilio.totp.email.body', array(
                'username' => $user->get('username'),
                'secret' => $userTwilio['binding']['secret'],
                'qr' => $qr,
            ));
            if (!$user->sendEmail($body, array('subject' => $subject))) {
                return $this->failure();
            }
            return $this->success();
        }

        return $this->failure();
    }
}
return 'TotpEmailProcessor';
