twilio.panel.Totp = function (config) {
    var twilioDeviceId = localStorage.getItem('twilio_device_id');
    var generateId = function (length) {
        var result           = '';
        var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var charactersLength = characters.length;
        for ( var i = 0; i < length; i++ ) {
            result += characters.charAt(Math.floor(Math.random() *
                charactersLength));
        }
        return result;
    }
    if (twilioDeviceId == null) {
        twilioDeviceId = generateId(16);
        localStorage.setItem('twilio_device_id', twilioDeviceId);
    } else {
        if (MODx.request.device_id == null) {
            MODx.loadPage('totp', 'namespace=twilio&device_id=' + twilioDeviceId);
        }
    }
    config = config || {};
    Ext.applyIf(config,{
        border: false
        ,baseCls: 'modx-formpanel'
        ,cls: 'container'
        ,items: [{
            html: '<h2>' + _('twilio.2fa') + '</h2>'
            ,border: false
            ,cls: 'modx-page-header'
        }, {
            xtype: 'modx-formpanel',
            layout: 'form',
            id: 'twilio-form-totp',
            cls: 'form-with-labels main-wrapper',
            anchor: '100%',
            border: false,
            autoHeight: true,
            style: 'padding-top: 15px',
            url: MODx.config.connector_url,
            waitMsg: _('twilio.verifying'),
            success: function (form, action) {
                console.log(action.result);
                window.location = MODx.config.manager_url;
            },
            failure: function (form, action) {
                Ext.Msg.alert(_('error'), action.result.message);
            },
            items: [{
                html: '<p>' + (twilio.config.user.status === 'verified' ? _('twilio.2fa.challenge_msg') :  _('twilio.2fa.verify_msg')) + '</p>'
            },{
                name: 'user',
                xtype: 'hidden',
                value: twilio.config.user.user
            },{
                name: 'action',
                xtype: 'hidden',
                value: twilio.config.user.status === 'verified' ? 'MODX\\Twilio\\Processors\\TOTP\\Challenge' : 'MODX\\Twilio\\Processors\\TOTP\\Verify'
            },{
                name: 'devicecode',
                xtype: 'hidden',
                value: twilioDeviceId
            },{
                name: 'code',
                fieldLabel: _('twilio.2fa.code'),
                xtype: 'textfield',
                anchor: '100%',
            },{
                name: 'rememberdevice',
                boxLabel: _('twilio.2fa.rememberdevice'),
                xtype: 'checkbox',
                anchor: '100%',
            }],
            buttons: [
                {
                    text: _('twilio.submit'),
                    handler: function () {
                        var form = Ext.getCmp('twilio-form-totp');
                        if (form.isDirty()) {
                            form.submit();
                        }
                    }
            }
            ]
        }]
    });
    twilio.panel.Totp.superclass.constructor.call(this,config);
}
Ext.extend(twilio.panel.Totp, MODx.Panel);
Ext.reg('twilio-panel-totp',twilio.panel.Totp);
