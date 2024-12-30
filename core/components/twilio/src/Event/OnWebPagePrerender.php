<?php

namespace MODX\Twilio\Event;

class OnWebPagePrerender extends Event
{
    public function run()
    {
        // System Wide
        $enforceTotp = $this->getOption('twilio.totp_enforce', false);
        $totpChallenge = (int)$this->getOption('twilio.totp_challenge_page', 0);
        $user = $this->modx->user;
        if (!$user || $user->id === 0 || $this->modx->resource->id === $totpChallenge) {
            return;
        }
        // User Specific
        $userTotp = $user->getOption('twilio.totp', $user->getSettings(), false);
        if ($enforceTotp && $userTotp && !$_SESSION['twilio_totp_verified'] && $totpChallenge > 0) {
            if ($this->modx->getOption('twilio.totp_email_on_login', null, false)) {
                $this->sendEmail($user);
            }
            $url = $this->modx->makeUrl($totpChallenge);
            $this->modx->sendRedirect($url);
        }
        if ($enforceTotp && $userTotp && !$_SESSION['twilio_totp_verified'] && $totpChallenge === 0) {
            if ($this->modx->getOption('twilio.totp_email_on_login', null, false)) {
                $this->sendEmail($user);
            }
            $url = $this->modx->getOption('manager_url');
            $this->modx->sendRedirect($url);
        }
    }

    private function sendEmail($user): void
    {
        $lang = $user->getOption('manager_language');
        $this->modx->lexicon->load("$lang:twilio:email");
        $code = $this->twilio->getCode($user);
        if ($code) {
            $subject = $this->modx->lexicon('twilio.totp.code.email.subject');
            $body = $this->modx->lexicon('twilio.totp.code.email.body', array(
                'username' => $user->get('username'),
                'code' => $code
            ));
            $user->sendEmail($body, array('subject' => $subject));
        }
    }
}
