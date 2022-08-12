<?php

class TwilioTotpStatusProcessor extends modProcessor
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
        $user = $this->modx->getObject('modUser', $id);

        if ($user) {
            $setting = $this->modx->getObject('modUserSetting', array('user' => $user->id, 'key' => 'twilio.totp'));
            if (!$setting) {
                $setting = $this->modx->newObject('modUserSetting');
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
return 'TwilioTotpStatusProcessor';
