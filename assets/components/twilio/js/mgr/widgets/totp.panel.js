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
            var url = 'namespace=twilio&device_id=' + twilioDeviceId;
            if (MODx.request.return) {
                url += '&return=' + MODx.request.return;
            }
            MODx.loadPage('totp', url);
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
            url: twilio.config.connector_url,
            waitMsg: _('twilio.verifying'),
            success: function (form, action) {
                var url = MODx.config.manager_url;
                if (MODx.request.return) {
                    var return_url = JSON.parse(decodeURIComponent(MODx.request.return));
                    console.log(return_url);
                    if (return_url.a) {
                        url += '?a=' + return_url.a;
                    }
                    if (return_url.namespace) {
                        url += '&namespace=' + return_url.namespace;
                    }
                    if (return_url.id) {
                        url += '&id=' + return_url.id;
                    }
                }
                window.location = url;
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
                value: twilio.config.user.status === 'verified' ? 'totp/challenge' :  'totp/verify'
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
