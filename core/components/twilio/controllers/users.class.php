<?php
require_once dirname(__FILE__, 2) . '/index.class.php';

class TwilioUsersManagerController extends TwilioBaseManagerController
{
    public function checkPermissions()
    {
        return $this->modx->hasPermission('twilio_manage_auth');
    }

    public function getPageTitle()
    {
        return $this->modx->lexicon('twilio.users');
    }

    public function process(array $scriptProperties = []) {}

    public function loadCustomCssJs()
    {
        $this->addCss($this->twilio->getOption('cssUrl') . 'mgr.css');
        $this->addJavascript($this->twilio->getOption('jsUrl') . 'mgr/helpers/combo.js?v=' . $this->version);
        $this->addJavascript($this->twilio->getOption('jsUrl') . 'mgr/widgets/users.grid.js?v=' . $this->version);
        $this->addJavascript($this->twilio->getOption('jsUrl') . 'mgr/widgets/users.panel.js?v=' . $this->version);
        $this->addLastJavascript($this->twilio->getOption('jsUrl') . 'mgr/sections/users.js?v=' . $this->version);
        $this->addHtml("<script>
        Ext.onReady(function() {
            MODx.load({ xtype: 'twilio-page-users'});
        });
        </script>");
    }
    public function getTemplateFile()
    {
        return $this->twilio->getOption('templatesPath') . 'users.tpl';
    }
}
