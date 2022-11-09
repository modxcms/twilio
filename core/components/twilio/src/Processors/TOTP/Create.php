<?php
namespace MODX\Twilio\Processor\TOTP;

use Twilio\Rest\Client;
use MODX\Revolution\Processors\Processor;
use MODX\Revolution\modUser;
use MODX\Revolution\modUserSetting;

class Create extends Processor
{
    public $languageTopics = array('twilio:default');
    public $objectType = 'twilio.totp';

    private string $sid;
    private string $token;
    private string $service;

    public function process()
    {
        $this->sid = $this->modx->getOption('twilio.account_sid');
        $this->token = $this->modx->getOption('twilio.account_token');
        $this->service = $this->modx->getOption('twilio.service_id');
        if (empty($this->sid) || empty($this->token) || empty($this->service)) {
            return $this->failure();
        }

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
            try {
                $twilio = new Client($this->sid, $this->token);
                $site = $this->modx->getOption('site_name');
                $verification_check = $twilio->verify->v2->services($this->service)
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
                        return false;
                    }
                    $setting = $this->modx->getObject(
                        modUserSetting::class,
                        array('user' => $user->id, 'key' => 'twilio.totp')
                    );
                    if (!$setting) {
                        $setting = $this->modx->newObject(modUserSetting::class);
                        $setting->set('user', $user->id);
                        $setting->set('key', 'twilio.totp');
                        $setting->set('xtype', 'combo-boolean');
                    }
                    $setting->set('value', 1);
                    $setting->save();
                    return true;
                }
                return false;
            } catch (\Exception $e) {
                $this->modx->log(xPDO::LOG_LEVEL_ERROR, "[Twilio Create TOTP] " . $e->getMessage());
                return false;
            }
        }

        return false;
    }
}
