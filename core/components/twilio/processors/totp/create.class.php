<?php

use Twilio\Rest\Client;

class TotpCreateProcessor extends modProcessor
{
    public $languageTopics = array('twilio:default');
    public $objectType = 'twilio.totp';

    public function process()
    {
        $sid = $this->modx->getOption('twilio.account_sid');
        $token = $this->modx->getOption('twilio.account_token');
        $service = $this->modx->getOption('twilio.service_id');
        if (empty($sid) || empty($token) || empty($service)) {
            return $this->failure();
        }

        $id = $this->getProperty('user');
        $user = $this->modx->getObject('modUser', $id);

        if ($user) {
            try {
                $twilio = new Client($sid, $token);
                $site = $this->modx->getOption('site_name');
                $verification_check = $twilio->verify->v2->services($service)
                    ->entities(str_pad($user->id, 8, '0', STR_PAD_LEFT))
                    ->newFactors
                    ->create($site, "totp");


                if ($verification_check->status === 'unverified') {
                    $this->modx->log(
                        xPDO::LOG_LEVEL_ERROR,
                        json_encode($verification_check->binding, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT)
                    );
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
                        $this->modx->log(xPDO::LOG_LEVEL_ERROR, "[Twilio Create TOTP] Failed to save profile");
                        return $this->failure();
                    }
                    $setting = $this->modx->getObject(
                        'modUserSetting',
                        array('user' => $user->id, 'key' => 'twilio.totp')
                    );
                    if (!$setting) {
                        $setting = $this->modx->newObject('modUserSetting');
                        $setting->set('user', $user->id);
                        $setting->set('key', 'twilio.totp');
                        $setting->set('xtype', 'combo-boolean');
                    }
                    $setting->set('value', 1);
                    $setting->save();
                    return $this->success('', $extended['twilio_totp']);
                }
                return false;
            } catch (\Exception $e) {
                $this->modx->log(xPDO::LOG_LEVEL_ERROR, "[Twilio Create TOTP] " . $e->getMessage());
                return $this->failure();
            }
        }

        return $this->failure();
    }
}
return 'TotpCreateProcessor';
