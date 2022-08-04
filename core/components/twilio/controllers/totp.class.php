<?php
require_once dirname(dirname(__FILE__)) . '/index.class.php';

class TwilioTotpManagerController extends TwilioBaseManagerController
{
    public function process(array $scriptProperties = array())
    {
        if (isset($_SESSION['twilio_totp_verified']) && $_SESSION['twilio_totp_verified']) {
            $this->modx->sendRedirect(MODX_MANAGER_URL);
        }
    }
    public function getPageTitle()
    {
        return $this->modx->lexicon('twilio');
    }
    public function loadCustomCssJs()
    {
        $latestVersion = '1';
        $this->addLastJavascript($this->twilio->getOption('jsUrl') . 'mgr/widgets/totp.panel.js?v=' . $latestVersion);
        $this->addLastJavascript($this->twilio->getOption('jsUrl') . 'mgr/sections/totp.js?v=' . $latestVersion);
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
