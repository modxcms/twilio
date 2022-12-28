<?php

namespace MODX\Twilio\Event;

use xPDO\xPDO;

class OnBeforeManagerPageInit extends Event
{
    public function run()
    {
        // System Wide
        $enforceTotp = $this->getOption('twilio.totp_enforce', false);
        // User Specific
        $userTotp = $this->getOption('twilio.totp', false);
        $action = $this->getOption('action');
        $user = $this->modx->user;
        if (!$user || $user->id === 0) {
            return false;
        }
        if ($enforceTotp && $userTotp && !$_SESSION['twilio_totp_verified'] && $action !== 'totp') {
            $this->modx->sendRedirect(MODX_MANAGER_URL . 'index.php?a=totp&namespace=twilio');
        }
    }
}
