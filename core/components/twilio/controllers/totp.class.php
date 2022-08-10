<?php

require_once dirname(dirname(__FILE__)) . '/index.class.php';
require_once dirname(dirname(__FILE__)) . '/lib/FixedBitNotation.php';

class TwilioTotpManagerController extends TwilioBaseManagerController
{
    public function process(array $scriptProperties = array())
    {
        if (isset($_SESSION['twilio_totp_verified']) && $_SESSION['twilio_totp_verified']) {
            $this->modx->sendRedirect(MODX_MANAGER_URL);
        }
        if ($this->modx->getOption('twilio.totp_email_on_login', null, false)) {
            $user = $this->modx->user;
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
}
