<?php

namespace MODX\Twilio\Snippet;

use MODX\Twilio\Utils;
use Twilio\Rest\Client;

class Verify extends Snippet
{
    public function process()
    {
        $sid = $this->modx->getOption('twilio.account_sid');
        $token = $this->modx->getOption('twilio.account_token');
        $service = $this->getOption('twilioServiceId', $this->modx->getOption('twilio.service_id'));

        if (empty($sid) || empty($token) || empty($service)) {
            $this->modx->sendErrorPage();
            return false;
        }

        $hook =& $this->sp['hook'];

        $code = $hook->getValue('code');
        $phone = $this->modx->getPlaceholder('twilio.phone');

        try {
            $twilio = new Client($sid, $token);
            $verification_check = $twilio->verify->v2->services($service)
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