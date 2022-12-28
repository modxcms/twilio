<?php

namespace MODX\Twilio\Event;

class OnBeforeManagerPageInit extends Event
{
    public function run()
    {
        // System Wide
        $enforceTotp = $this->getOption('twilio.totp_enforce', false);
        $action = $this->getOption('action');
        $user = $this->modx->user;
        if (!$user || $user->id === 0) {
            return false;
        }
        // User Specific
        $userTotp = $user->getOption('twilio.totp', $user->getSettings(), false);
        if ($enforceTotp && $userTotp && !$_SESSION['twilio_totp_verified'] && $action['controller'] !== 'totp') {
            $this->modx->sendRedirect(MODX_MANAGER_URL . 'index.php?a=totp&namespace=twilio');
        }
    }
}
