<?php

namespace MODX\Twilio\Snippet;

use Twilio\Rest\Client;

class TotpCreate extends Snippet
{
    public function process()
    {
        $sid = $this->modx->getOption('twilio.account_sid');
        $token = $this->modx->getOption('twilio.account_token');
        $service = $this->getOption('twilioServiceId', $this->modx->getOption('twilio.service_id'));
        $status = ($this->modx->getOption('status', $_REQUEST, null) === 'disable_totp') ? 0 : 1;

        if (empty($sid) || empty($token) || empty($service)) {
            $this->modx->sendErrorPage();
            return false;
        }
        $user = $this->modx->user;
        if (!$user || $user->id === 0) {
            $this->modx->sendErrorPage();
            return false;
        }

        $setting = $this->modx->getObject('modUserSetting', array('user' => $user->id, 'key' => 'twilio.totp'));
        if (!$setting) {
            $setting = $this->modx->newObject('modUserSetting');
            $setting->set('user', $user->id);
            $setting->set('key', 'twilio.totp');
            $setting->set('xtype', 'combo-boolean');
        }
        $this->modx->log(\xPDO::LOG_LEVEL_ERROR, "[Twilio Create TOTP] setting = ".$status." REQUEST = ".$_REQUEST['status']);
        if ($status === 0) {
            $setting->set('value', $status);
            if (!$setting->save()) {
                $this->modx->log(\xPDO::LOG_LEVEL_ERROR, "[Twilio Create TOTP] Failed to save user setting");
                return false;
            }
            $this->redirect();
            return true;
        }

        try {
            $twilio = new Client($sid, $token);
            $site = $this->modx->getOption('site_name');
            $verification_check = $twilio->verify->v2->services($service)
                ->entities(str_pad($user->id, 8, '0', STR_PAD_LEFT))
                ->newFactors
                ->create($site, "totp");


            if ($verification_check->status === 'unverified') {
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
                    $this->modx->log(\xPDO::LOG_LEVEL_ERROR, "[Twilio Create TOTP] Failed to save profile");
                    return false;
                }
                $setting->set('value', $status);
                if (!$setting->save()) {
                    $this->modx->log(\xPDO::LOG_LEVEL_ERROR, "[Twilio Create TOTP] Failed to save user setting");
                    return false;
                }
                $_SESSION['twilio_totp_verified'] = true;
                $this->redirect();
                return true;
            }
            return false;
        } catch (\Exception $e) {
            $this->modx->log(\xPDO::LOG_LEVEL_ERROR, "[Twilio Create TOTP] " . $e->getMessage());
            return false;
        }
    }
}
