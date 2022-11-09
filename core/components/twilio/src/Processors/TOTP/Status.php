<?php
namespace MODX\Twilio\Processor\TOTP;

use MODX\Revolution\Processors\Processor;
use MODX\Revolution\modUser;
use MODX\Revolution\modUserSetting;

class Status extends Processor
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
        $status = $this->getProperty('status');
        $user = $this->modx->getObject(modUser::class, $id);

        if ($user) {
            $setting = $this->modx->getObject(modUserSetting::class, array('user' => $user->id, 'key' => 'twilio.totp'));
            if (!$setting) {
                $setting = $this->modx->newObject(modUserSetting::class);
                $setting->set('user', $user->id);
                $setting->set('key', 'twilio.totp');
                $setting->set('xtype', 'combo-boolean');
            }
            $setting->set('value', $status);
            $setting->save();

            return true;
        }

        return false;
    }
}
