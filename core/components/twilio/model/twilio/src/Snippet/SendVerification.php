<?php
namespace MODX\Twilio\Snippet;

use MODX\Twilio\Utils;
use Twilio\Rest\Client;

class SendVerification extends Snippet {

    public function process()
    {
        $this->modx->setPlaceholder("twilio.code_sent", '');

        $sid = $this->modx->getOption('twilio.account_sid');
        $token = $this->modx->getOption('twilio.account_token');
        $service = $this->getOption('twilioServiceId', $this->modx->getOption('twilio.service_id'));

        if (empty($sid) || empty($token) || empty($service)) {
            $this->modx->sendErrorPage();
            return false;
        }

        $hook = $this->sp['hook'];
        $allowedChannels = $this->getOption('twilioAllowedChannels', 'call,sms', true);
        $allowedChannels = Utils::explodeAndClean($allowedChannels);
        $limit = intval($this->getOption('twilioSendLimit', '15')) * 60; // to minutes

        $channel = $hook->getValue('channel');
        if (!in_array($channel, $allowedChannels)) {
            $hook->addError('channel', "Invalid channel");
            return false;
        }

        $username = $this->base64urlDecode($_REQUEST['lu']);

        /** @var \modUser $user */
        $user = $this->modx->getObject('modUser', ['username' => $username]);

        /** @var \modUserProfile $profile */
        $profile = $user->getOne('Profile');

        $extended = $profile->get('extended');
        $lastSend = !empty($extended['twilio_last_send']) ? intval($extended['twilio_last_send']) : 0;
        $now = time();

        if ($limit !== 0 && $lastSend !== 0 && ($lastSend + $limit) > $now) {
            $nextIn = round(($lastSend + $limit - $now) / 60);
            $nextText = $nextIn > 1 ? ($nextIn . ' minutes') : 'a minute';
            $hook->addError('channel', "Code was requested recently, another code can be requested in about {$nextText}");
            return false;
        }

        try {
            $twilio = new Client($sid, $token);

            $verification = $twilio->verify->v2->services($service)
                ->verifications
                ->create($this->modx->getPlaceholder('twilio.phone'), $channel);

            if ($verification->status !== 'pending') {
                $hook->addError('channel', "Requesting verification code failed.");
                return false;
            }

            $extended['twilio_last_send'] = $now;
            $profile->set('extended', $extended);
            $profile->save();

            $this->modx->setPlaceholder("twilio.code_sent", 'Verification code was requested. Please enter it below.');
            return true;
        } catch (\Exception $e) {
            $hook->addError('channel', "Requesting verification code failed.");
            return false;
        }
    }
}