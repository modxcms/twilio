<?php

namespace MODX\Twilio\Snippet;

use Twilio\Rest\Client;

class CreateTotp extends Snippet
{

    public function process()
    {
        $sid = $this->modx->getOption('twilio.account_sid');
        $token = $this->modx->getOption('twilio.account_token');
        $service = $this->getOption('twilioServiceId', $this->modx->getOption('twilio.service_id'));

        if (empty($sid) || empty($token) || empty($service)) {
            $this->modx->sendErrorPage();
            return false;
        }
        $user = $this->modx->user;
        if (!$user || $user->id === 0) {
            return false;
        }

        try {
            $twilio = new Client($sid, $token);
            $verification_check = $twilio->verify->v2->services($service)
                ->entities(str_pad($user->id, 8, '0', STR_PAD_LEFT))
                ->newFactors
                ->create("User $user->id", "totp");


            if ($verification_check->status === 'unverified') {
                $this->modx->log(1, json_encode($verification_check->binding, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
                $profile = $user->getOne('Profile');
                $extended = $profile->get('extended');
                if (!is_array($extended)) {
                    $extended = [];
                }
                $extended['twilio_totp'] = $verification_check->toArray();
                unset(
                    $extended['twilio_totp']['accountSid'],
                    $extended['twilio_totp']['serviceSid'],
                    $extended['twilio_totp']['config']
                );
                $profile->set('extended', $extended);
                if (!$profile->save()) {
                    $this->modx->log(1, "[Twilio Create TOTP] Failed to save profile");
                }
            }
            return false;
        } catch (\Exception $e) {
            $this->modx->log(1, "[Twilio Create TOTP] " . $e->getMessage());
            return false;
        }
    }
}
