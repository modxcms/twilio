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
            $code = $this->getCode($user);
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

    public function getCode($user)
    {
        $profile = $user->getOne('Profile');
        $extended = $profile->get('extended');
        $secret = $extended['twilio_totp']['binding']['secret'] ?? null;
        if ($secret) {
            $base32 = new FixedBitNotation(5, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567', true, true);
            $secret = $base32->decode($secret);
            $time = floor(time() / 30);
            $time = pack("N", $time);
            $time = str_pad($time, 8, chr(0), STR_PAD_LEFT);
            $hash = hash_hmac('sha1', $time, $secret, true);
            $offset = ord(substr($hash, -1));
            $offset &= 0xF;

            $truncatedHash = substr($hash, $offset);
            $truncatedHash = unpack("N", substr($truncatedHash, 0, 4));
            $truncatedHash = $truncatedHash[1] & 0x7FFFFFFF;

            return str_pad($truncatedHash % (10 ** 6), 6, "0", STR_PAD_LEFT);
        }
        return null;
    }
}
