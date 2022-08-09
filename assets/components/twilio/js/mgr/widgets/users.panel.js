twilio.panel.Users = function (config) {
    config = config || {};
    Ext.applyIf(config,{
        border: false
        ,baseCls: 'modx-formpanel'
        ,cls: 'container'
        ,items: [{
            html: '<h2>' + _('twilio.users') + '</h2>'
            ,border: false
            ,cls: 'modx-page-header'
        }, {
            xtype: 'twilio-grid-users',
        }]
    });
    twilio.panel.Users.superclass.constructor.call(this,config);
}
Ext.extend(twilio.panel.Users, MODx.Panel);
Ext.reg('twilio-panel-users',twilio.panel.Users);
