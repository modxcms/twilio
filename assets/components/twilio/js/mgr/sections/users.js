twilio.page.Users = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        components: [{
            xtype: 'twilio-panel-users',
            renderTo: 'twilio-panel-users-div'
        }]
    });
    twilio.page.Users.superclass.constructor.call(this,config);
}
Ext.extend(twilio.page.Users,MODx.Component);
Ext.reg('twilio-page-users',twilio.page.Users);
