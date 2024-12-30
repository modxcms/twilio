<?php

namespace MODX\Twilio\Processors\TOTP;

use MODX\Revolution\modUser;
use MODX\Revolution\Processors\Processor;
use Twilio\Rest\Client;

class Verify extends Processor
{
    public $languageTopics = array('twilio:default');
    public $objectType = 'twilio.totp';

    public function process()
    {
        $sid = $this->modx->getOption('twilio.account_sid');
        $token = $this->modx->getOption('twilio.account_token');
        $service = $this->modx->getOption('twilio.service_id');
        if (empty($sid) || empty($token) || empty($service)) {
            return $this->failure('Missing configuration');
        }

        $id = $this->getProperty('user');
        $code = $this->getProperty('code');
        $deviceCode = $this->getProperty('devicecode');
        $remember = $this->getProperty('rememberdevice');

        $user = $this->modx->getObject(modUser::class, $id);
        if ($user) {
            $profile = $user->getOne('Profile');
            $extended = $profile->get('extended');
            $userTwilio = $extended['twilio_totp'];
            try {
                $twilio = new Client($sid, $token);
                $verification_check = $twilio->verify->v2->services($service)
                    ->entities(str_pad($user->id, 8, '0', STR_PAD_LEFT))
                    ->factors($userTwilio['sid'])
                    ->update(["authPayload" => $code]);


                if ($verification_check->status === 'verified') {
                    if (!empty($deviceCode) && !empty($remember)) {
                        if (empty($userTwilio['remembered'])) {
                            $userTwilio['remembered'] = array();
                        }
                        $userTwilio['remembered'][] = $deviceCode;
                    }
                    $userTwilio['status'] = 'verified';
                    $extended['twilio_totp'] = $userTwilio;
                    $profile->set('extended', $extended);
                    $profile->set('failedlogincount', 0);
                    if ($profile->save()) {
                        $_SESSION['twilio_totp_verified'] = true;
                    }
                    return $this->success();
                }
                return $this->failure('Invalid code');
            } catch (\Exception $e) {
                $message = $e->getMessage();
                if (strpos($message, 'HTTP 404') !== false) {
                    $canRegenerate = $this->modx->getOption('twilio.totp_allow_expired', '0') === '1';
                    if ($canRegenerate) {
                        $regenerate = $this->modx->runProcessor(Create::class, ['user' => $id]);
                        if ($regenerate) {
                            $email = $this->modx->runProcessor(Email::class, ['user' => $id]);
                            if ($email) {
                                return $this->failure($this->modx->lexicon('twilio.error.code_regenerated'));
                            }
                        }
                        return $this->failure($this->modx->lexicon('twilio.error.code_expired'));
                    }
                }
                return $this->failure($message);
            }
        }
        return $this->failure($this->modx->lexicon('twilio.error.no_user'));
    }
}
