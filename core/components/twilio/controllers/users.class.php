<?php
require_once dirname(dirname(__FILE__)) . '/index.class.php';

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
    public function loadCustomCssJs()
    {
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
