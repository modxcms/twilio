<?php

use Twilio\Rest\Client;

class TotpVerifyProcessor extends modProcessor
{
    public $languageTopics = array('twilio:default');
    public $objectType = 'twilio.totp';

    public function process()
    {
        $sid = $this->modx->getOption('twilio.account_sid');
        $token = $this->modx->getOption('twilio.account_token');
        $service = $this->modx->getOption('twilio.service_id');
        if (empty($sid) || empty($token) || empty($service)) {
            return $this->failure($this->modx->lexicon('twilio.error.missing_configuration'));
        }

        $id = $this->getProperty('user');
        $code = $this->getProperty('code');
        $deviceCode = $this->getProperty('devicecode');
        $remember = $this->getProperty('rememberdevice');

        $user = $this->modx->getObject('modUser', $id);
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
                return $this->failure($this->modx->lexicon('twilio.error.invalid_code'));
            } catch (\Exception $e) {
                $message = $e->getMessage();
                if (strpos($message, 'HTTP 404') !== false) {
                    $canRegenerate = $this->modx->getOption('twilio.totp_allow_expired', '0') === '1';
                    if ($canRegenerate) {
                        $corePath = $this->modx->getOption('twilio.core_path', null, $this->modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/twilio/');
                        $service = $this->modx->getService(
                            'twilio',
                            'Twilio',
                            $corePath . 'model/twilio/',
                            array(
                                'core_path' => $corePath
                            )
                        );
                        $regenerate = $this->modx->runProcessor(
                            'totp/create',
                            ['user' => $id],
                            ['processors_path' => $service->getOption('processorsPath', null, $corePath . 'processors/')]
                        );
                        if ($regenerate) {
                            $email = $this->modx->runProcessor(
                                'totp/email',
                                ['user' => $id],
                                ['processors_path' => $service->getOption('processorsPath', null, $corePath . 'processors/')]
                            );
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
return 'TotpVerifyProcessor';
