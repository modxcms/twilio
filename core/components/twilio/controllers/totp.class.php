<?php

require_once dirname(dirname(__FILE__)) . '/index.class.php';
require_once dirname(dirname(__FILE__)) . '/lib/FixedBitNotation.php';

class TwilioTotpManagerController extends TwilioBaseManagerController
{
    public function process(array $scriptProperties = array())
    {
        $deviceID = $_REQUEST['device_id'] ?? null;
        if (isset($_SESSION['twilio_totp_verified']) && $_SESSION['twilio_totp_verified']) {
            $this->modx->sendRedirect($this->getManagerUrl());
        }
        $user = $this->modx->user;
        $this->checkDevice($deviceID, $user);
        if ($this->modx->getOption('twilio.totp_email_on_login', null, false) && !empty($deviceID)) {
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
    public function getPageTitle()
    {
        return $this->modx->lexicon('twilio.2fa');
    }
    public function loadCustomCssJs()
    {
        $this->addCss($this->twilio->getOption('cssUrl') . '2fa.css');
        $this->addJavascript($this->twilio->getOption('jsUrl') . 'mgr/widgets/totp.panel.js?v=' . $this->version);
        $this->addLastJavascript($this->twilio->getOption('jsUrl') . 'mgr/sections/totp.js?v=' . $this->version);
        $this->addHtml("<script>
        Ext.onReady(function() {
            Ext.getCmp('modx-layout').hideLeftbar(false, false);
            var leftBar = Ext.getCmp('modx-leftbar-tabpanel');
            leftBar.hide();
            MODx.load({ xtype: 'twilio-page-totp'});
        });
        </script>");
    }
    public function getTemplateFile()
    {
        return $this->twilio->getOption('templatesPath') . 'totp.tpl';
    }

    public function checkDevice($device, $user)
    {
        $profile = $user->getOne('Profile');
        $extended = $profile->get('extended');
        $userTwilio = $extended['twilio_totp'];
        if (
            !empty($device) &&
            !empty($userTwilio['remembered']) &&
            in_array($device, $userTwilio['remembered'], true)
        ) {
            $_SESSION['twilio_totp_verified'] = true;
            $this->modx->sendRedirect($this->getManagerUrl());
        } elseif (!empty($device)) {
            $failed = $profile->get('failedlogincount') ?? 0;
            ++$failed;
            if ($failed > $this->modx->getOption('failed_login_attempts', null, 5)) {
                $profile->set('blockeduntil', time() + ($this->modx->getOption('blocked_minutes', null, 60) * 60));
                $profile->set('blocked', 1);
                $failed = 0;
            }
            $profile->set('failedlogincount', $failed);
            $profile->save();
            if ($profile->get('blocked') === 1) {
                $this->modx->runProcessor('security/logout');
                $this->modx->sendRedirect(MODX_MANAGER_URL);
            }
        }
    }

    private function getManagerUrl()
    {
        $url = MODX_MANAGER_URL;
        if ($_REQUEST['return']) {
            $url .= '?';
            $return = json_decode($_REQUEST['return'], true);
            if (isset($return['a'])) {
                $url .= '&a='.$return['a'];
            }
            if (isset($return['namespace'])) {
                $url .= '&namespace='.$return['namespace'];
            }
            if (isset($return['id'])) {
                $url .= '&id='.$return['id'];
            }
        }
        return $url;
    }
}
