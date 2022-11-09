<?php
namespace MODX\Twilio\Processors\TOTP;

use MODX\Revolution\Processors\Processor;
use MODX\Revolution\modUser;

class Clear extends Processor
{
    public $languageTopics = array('twilio:default');
    public $objectType = 'twilio.totp';

    public function process()
    {
        $id = $this->getProperty('user');

        $user = $this->modx->getObject(modUser::class, $id);

        if ($user) {
            $profile = $user->getOne('Profile');
            $extended = $profile->get('extended');
            unset($extended['twilio_totp']);
            $profile->set('extended', $extended);
            if ($profile->save()) {
                return $this->success();
            }
        }
        return $this->failure();
    }
}
