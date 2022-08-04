<?php

namespace MODX\Twilio\Event;

class OnBeforeManagerPageInit extends Event
{
    public function run()
    {
        $enforceTotp = $this->getOption('twilio.enforce_totp', false);
        $action = $this->getOption('action');
        $user = $this->modx->user;
        if(!$user || $user->id === 0) {
            return false;
        }
        if ($enforceTotp && !$_SESSION['twilio_totp_verified'] && $action['controller'] !== 'totp') {
            $this->modx->sendRedirect(MODX_MANAGER_URL . 'index.php?a=totp&namespace=twilio');
        }
    }
}
