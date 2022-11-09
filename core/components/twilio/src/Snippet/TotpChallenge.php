<?php

namespace MODX\Twilio\Snippet;

use Twilio\Rest\Client;

class TotpChallenge extends Snippet
{
    private string $sid;
    private string $token;
    private string $service;

    public function process()
    {
        $this->sid = $this->modx->getOption('twilio.account_sid');
        $this->token = $this->modx->getOption('twilio.account_token');
        $this->service = $this->getOption('twilioServiceId', $this->modx->getOption('twilio.service_id'));
        $hook =& $this->sp['hook'];
        $code = $hook->getValue('code');

        if (empty($this->sid) || empty($this->token) || empty($this->service)) {
            $this->modx->sendErrorPage();
            return false;
        }

        $user = $this->modx->user;
        if (!$user || $user->id === 0) {
            $this->modx->sendErrorPage();
            return false;
        }
        $profile = $user->getOne('Profile');
        $extended = $profile->get('extended');
        $userTwilio = $extended['twilio_totp'];
        if ($userTwilio['status'] !== 'verified') {
            return true;
        }
        try {
            $twilio = new Client($this->sid, $this->token);
            $verification_check = $twilio->verify->v2->services($this->service)
                ->entities(str_pad($user->id, 8, '0', STR_PAD_LEFT))
                ->challenges
                ->create($userTwilio['sid'], ["authPayload" => $code]);


            if ($verification_check->status === 'approved') {
                $profile->set('extended', $extended);
                if ($profile->save()) {
                    $_SESSION['twilio_totp_verified'] = true;
                }
                $this->redirect();
                return true;
            }
            $hook->addError('code', 'Invalid code');
            return false;
        } catch (\Exception $e) {
            $hook->addError('code', 'Challenge failed.');
            return false;
        }
    }
}
