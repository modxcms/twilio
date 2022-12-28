<?php

class TotpEmailProcessor extends modProcessor
{
    public $languageTopics = array('twilio:default');
    public $objectType = 'twilio.totp';

    public function process()
    {
        $ids = explode(',', $this->getProperty('user')) ?? [];
        $success = true;
        foreach ($ids as $id) {
            if (!$this->handleUser($id)) {
                $success = false;
            }
        }
        return $success ? $this->success() : $this->failure();
    }

    public function handleUser($id): bool
    {
        $user = $this->modx->getObject('modUser', $id);
        if ($user) {
            $setting = $this->modx->getObject('modUserSetting', array('user' => $user->id, 'key' => 'twilio.totp'));
            if (!$setting || !$setting->get('value')) {
                return true;
            }
            $profile = $user->getOne('Profile');
            $extended = $profile->get('extended');
            $userTwilio = $extended['twilio_totp'];
            $uri = $userTwilio['binding']['uri'];
            $qr = 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=' . urlencode($uri);
            $lang = $user->getOption('manager_language');
            $this->modx->lexicon->load("$lang:twilio:email");
            $subject = $this->modx->lexicon('twilio.totp.qr.email.subject');
            $body = $this->modx->lexicon('twilio.totp.qr.email.body', array(
                'username' => $user->get('username'),
                'secret' => $userTwilio['binding']['secret'],
                'qr' => $qr,
            ));
            if (!$user->sendEmail($body, array('subject' => $subject))) {
                return false;
            }
            return true;
        }

        return false;
    }
}
return 'TotpEmailProcessor';