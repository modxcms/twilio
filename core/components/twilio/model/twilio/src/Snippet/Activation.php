<?php
namespace MODX\Twilio\Snippet;

class Activation extends Snippet {

    public function process()
    {
        $hook = $this->sp['hook'];

        $redirectToActivationResource = intval($this->getOption('twilioRedirectToActivationResource', 1)) === 1;
        $activationResource = $this->getOption('twilioActivationResourceId',1);
        $activationTTL = intval($this->getOption('twilioActivationTTL', 180)); // in minutes
        if (empty($activationTTL)) {
            $activationTTL = 180;
        }

        /** @var \modUser $user */
        $user = $hook->getValue('register.user');

        /** @var \modUserProfile $profile */
        $profile = $hook->getValue('register.profile');

        $tempPassword = $this->modx->user->generatePassword();
        $confirmParams['lp'] = $this->base64urlEncode($tempPassword);
        $confirmParams['lu'] = $this->base64urlEncode($user->get('username'));

        $confirmUrl = $this->modx->makeUrl($activationResource,'', $confirmParams,'full');

        $emailTpl = $this->getOption('twilioActivationEmailTpl','');
        if (!empty($emailTpl)) {
            $emailProperties = [
                'confirmUrl' => $confirmUrl,
                'fullname' => $profile->fullname,
                'email' => $profile->email,
                'username' => $user->username,
                'id' => $user->id,
            ];

            $msg = $this->modx->getChunk($emailTpl, $emailProperties);
            $subject = $this->getOption('twilioActivationEmailSubject', 'Activate your account');

            $user->sendEmail($msg, [
                'from' => $this->getOption('twilioEmailFrom', $this->modx->getOption('emailsender'), true),
                'fromName' => $this->getOption('twilioEmailFromName', $this->modx->getOption('site_name'), true),
                'sender' => $this->getOption('twilioEmailSender', $this->modx->getOption('emailsender'), true),
                'subject' => $subject,
                'html' => true,
            ]);
        }

        $this->modx->getService('registry', 'registry.modRegistry');
        $this->modx->registry->addRegister('twilio','registry.modFileRegister');
        $this->modx->registry->twilio->connect();
        $this->modx->registry->twilio->subscribe('/activation/');
        $this->modx->registry->twilio->send('/activation/', [$user->get('username') => $tempPassword], [
            'ttl' => $activationTTL * 60,
        ]);

        /* set cachepwd here to prevent re-registration of inactive users */
        $user->set('cachepwd', md5($tempPassword));
        $user->save();

        if ($redirectToActivationResource) {
            $this->modx->sendRedirect($confirmUrl);
        }
    }
}