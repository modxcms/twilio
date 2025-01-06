<?php

namespace MODX\Twilio\v2\Snippet;

use MODX\Twilio\Utils;
use Twilio\Rest\Client;

class Verify extends Snippet
{
    private string $sid;
    private string $token;
    private string $service;

    private bool $phoneFromSession = false;

    public function process()
    {
        $this->sid = $this->modx->getOption('twilio.account_sid');
        $this->token = $this->modx->getOption('twilio.account_token');
        $this->service = $this->getOption('twilioServiceId', $this->modx->getOption('twilio.service_id'));
        $this->key = $this->modx->getOption('twilio.encryption_key');

        if (empty($this->sid) || empty($this->token) || empty($this->service)) {
            $this->modx->sendErrorPage();
            return false;
        }
        $factorType = $this->getOption('twilioFactorType', 'phone');
        if ($factorType === 'phone') {
            return $this->verifyPhone();
        } elseif ($factorType === 'totp') {
            return $this->verifyTotp();
        } else {
            return false;
        }
    }

    private function verifyPhone(): bool
    {
        $hook =& $this->sp['hook'];
        $code = $hook->getValue('code');
        $phone = $this->modx->getPlaceholder('twilio.phone');
        $this->phoneFromSession = intval($this->getOption('twilioPhoneFromSession', '0')) === 1;
        $twilioPersistPhone = $this->getOption('twilioPersistPhone', '');

        if ($this->phoneFromSession) {
            $phone = $_SESSION['twilio_phone'];
        }

        try {
            $twilio = new Client($this->sid, $this->token);
            $verification_check = $twilio->verify->v2->services($this->service)
                ->verificationChecks
                ->create($code, ["to" => $phone]);


            if ($verification_check->status === 'approved') {
                /** @var \modUser $user */
                $user = $this->getUser();

                if (empty($twilioPersistPhone)) {
                    $user->set('active', true);
                    $user->_fields['cachepwd'] = '';
                    $user->setDirty('cachepwd');
                    $user->save();

                    $this->modx->invokeEvent('OnUserActivate', [
                        'user' => &$user,
                    ]);
                } else {
                    if ($twilioPersistPhone !== 'phone') {
                        $twilioPersistPhone = 'mobilephone';
                    }

                    $profile = $user->getOne('Profile');
                    $profile->set($twilioPersistPhone, $phone);
                    $profile->save();

                    $this->modx->invokeEvent('OnUserSave', [
                        'user' => &$user,
                        'mode' => 'upd'
                    ]);

                    unset($_SESSION['twilio_phone']);
                }

                if (!$this->phoneFromSession) {
                    $this->autoLogIn($user);
                }

                $this->redirect();

                return true;
            }

            $hook->addError('code', 'Invalid code');
            return false;
        } catch (\Exception $e) {
            $hook->addError('code', 'Verification failed.');
            return false;
        }
    }

    private function verifyTotp(): bool
    {
        $hook =& $this->sp['hook'];
        $code = $hook->getValue('code');
        $user = $this->modx->user;
        if (!$user || $user->id === 0) {
            $hook->addError('code', 'User not found');
            return false;
        }
        $profile = $user->getOne('Profile');
        $extended = $profile->get('extended');
        $userTwilio = $extended['twilio_totp'];

        try {
            $twilio = new Client($this->sid, $this->token);
            $verification_check = $twilio->verify->v2->services($this->service)
                ->entities(str_pad($user->id, 8, '0', STR_PAD_LEFT))
                ->factors($userTwilio['sid'])
                ->update(["authPayload" => $code]);


            if ($verification_check->status === 'verified') {
                $extended['twilio_totp']['status'] = 'verified';
                $profile->set('extended', $extended);
                if ($profile->save()) {
                    $setting = $this->modx->getObject(
                        'modUserSettings',
                        array('user' => $user->id, 'key' => 'twilio.totp')
                    );
                    if (!$setting) {
                        $setting = $this->modx->newObject('modUserSettings');
                        $setting->set('user', $user->id);
                        $setting->set('key', 'twilio.totp');
                        $setting->set('xtype', 'combo-boolean');
                    }
                    $setting->set('value', 1);
                    $setting->save();
                    $_SESSION['twilio_totp_verified'] = true;
                    $this->redirect();
                }

                return true;
            }

            $hook->addError('code', 'Invalid code');
            return false;
        } catch (\Exception $e) {
            $hook->addError('code', 'Verification failed.');
            return false;
        }
    }

    private function getUser()
    {
        if ($this->phoneFromSession) {
            return $this->modx->user;
        }

        $username = $this->base64urlDecode($_REQUEST['lu']);
        /** @var \modUser $user */
        $user = $this->modx->getObject('modUser', ['username' => $username]);

        /** @var \modFileRegister $reg */
        $reg = $this->getRegister();
        $reg->connect();
        $reg->subscribe('/activation/' . $user->get('username'));
        $reg->read();

        return $user;
    }

    private function autoLogIn($user)
    {
        $autoLogIn = (int)$this->getOption('twilioAutoLogIn', 1) === 1;
        $contexts = $this->getOption('twilioAuthenticateContexts', $this->modx->context->get('key'));

        if (!$autoLogIn) {
            return;
        }

        $this->modx->user =& $user;
        $this->modx->getUser();
        $contexts = Utils::explodeAndClean($contexts);
        foreach ($contexts as $ctx) {
            $this->modx->user->addSessionContext($ctx);
        }
    }
}
