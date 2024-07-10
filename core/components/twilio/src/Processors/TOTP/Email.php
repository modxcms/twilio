<?php
namespace MODX\Twilio\Processors\TOTP;

use MODX\Revolution\Processors\Processor;
use MODX\Revolution\modUser;
use MODX\Revolution\modUserSetting;

class Email extends Processor
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
        $user = $this->modx->getObject(modUser::class, $id);
        if ($user) {
            $setting = $this->modx->getObject(modUserSetting::class, array('user' => $user->id, 'key' => 'twilio.totp'));
            if (!$setting || !$setting->get('value')) {
                return true;
            }
            $profile = $user->getOne('Profile');
            $extended = $profile->get('extended');
            if (!isset($extended['twilio_totp'])) {
                return false;
            }
            $userTwilio = $extended['twilio_totp'];
            $uri = $userTwilio['binding']['uri'];
            $qr = (new \chillerlan\QRCode\QRCode)->render($uri);
            $lang = $user->getOption('manager_language');
            if ($lang) {
                $this->modx->lexicon->load("$lang:twilio:email");
            } else {
                $this->modx->lexicon->load("twilio:email");
            }
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
