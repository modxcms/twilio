twilio.page.Totp = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        components: [{
            xtype: 'twilio-panel-totp',
            renderTo: 'twilio-panel-totp-div'
        }]
    });
    twilio.page.Totp.superclass.constructor.call(this,config);
}
Ext.extend(twilio.page.Totp,MODx.Component);
Ext.reg('twilio-page-totp',twilio.page.Totp);
