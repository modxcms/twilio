<?php

namespace MODX\Twilio\Event;

class OnWebPagePrerender extends Event
{
    public function run()
    {
        $enforceTotp = $this->getOption('twilio.totp_enforce', false);
        $totpChallenge = (int) $this->getOption('twilio.totp_challenge_page', 0);
        $user = $this->modx->user;
        if (!$user || $user->id === 0 || $this->modx->resource->id === $totpChallenge) {
            return;
        }
        if ($enforceTotp && !$_SESSION['twilio_totp_verified'] && $totpChallenge > 0) {
            $url = $this->modx->makeUrl($totpChallenge);
            $this->modx->sendRedirect($url);
        }
    }
}
