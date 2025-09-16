<?php

namespace MODX\Twilio\Event;

class OnBeforeManagerPageInit extends Event
{
    public function run()
    {
        // System Wide
        $enforceTotp = $this->getOption('twilio.totp_enforce', false);
        $action = $this->getOption('action', $_GET['a']);
        if (is_array($action) && isset($action['controller'])) {
            $action = $action['controller'];
        }
        $namespace = $this->getOption('namespace', $_GET['namespace']);
        $user = $this->modx->user;
        if (!$user || $user->id === 0) {
            return false;
        }
        // User Specific
        $userTotp = $user->getOption('twilio.totp', $user->getSettings(), false);
        if ($enforceTotp && $userTotp && !$_SESSION['twilio_totp_verified'] && $action !== 'totp') {
            $return = [];
            if (isset($action)) {
                $return['a'] = $action;
            }
            if (isset($namespace)) {
                $return['namespace'] = $namespace;
            }
            if (isset($_REQUEST['id'])) {
                $return['id'] = $_REQUEST['id'];
            }
            $this->modx->sendRedirect(MODX_MANAGER_URL . 'index.php?a=totp&namespace=twilio&return=' . json_encode($return));
        }
    }
}
