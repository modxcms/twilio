<?php

namespace MODX\Twilio\Snippet;

use MODX\Twilio\Utils;
use Twilio\Rest\Client;

class Verify extends Snippet
{
    private string $sid;
    private string $token;
    private string $service;

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

        try {
            $twilio = new Client($this->sid, $this->token);
            $verification_check = $twilio->verify->v2->services($this->service)
                ->verificationChecks
                ->create($code, ["to" => $phone]);


            if ($verification_check->status === 'approved') {
                /** @var \modUser $user */
                $user = $this->getUser();

                $user->set('active', true);
                $user->_fields['cachepwd'] = '';
                $user->setDirty('cachepwd');
                $user->save();

                $this->modx->invokeEvent('OnUserActivate', [
                    'user' => &$user,
                ]);

                $this->autoLogIn($user);
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
                ->challenges
                ->create($userTwilio['sid'], ["authPayload" => $code]);


            if ($verification_check->status === 'approved') {
                $extended['twilio_totp']['status'] = 'verified';
                $profile->set('extended', $extended);
                if ($profile->save()) {
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
        $username = $this->base64urlDecode($_REQUEST['lu']);
        /** @var \modUser $user */
        $user = $this->modx->getObject('modUser', ['username' => $username]);

        $this->modx->getService('registry', 'registry.modRegistry');
        $this->modx->registry->addRegister('twilio', 'registry.modFileRegister');

        /** @var \modRegister $reg */
        $reg = $this->modx->registry->twilio;
        $reg->connect();
        $reg->subscribe('/activation/' . $user->get('username'));
        $reg->read();

        return $user;
    }

    private function autoLogIn(\modUser $user)
    {
        $autoLogIn = intval($this->getOption('twilioAutoLogIn', 1)) === 1;
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

    private function redirect()
    {
        $redirect = (int)$this->getOption('twilioRedirect', 0);
        if (!empty($redirect)) {
            $this->modx->sendRedirect($this->modx->makeUrl($redirect));
        }
    }
}
