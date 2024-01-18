twilio.combo.UserActive = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        store: new Ext.data.SimpleStore({
            fields: ["l", "v"],
            data: [
                ["All", ""],
                ["Active", '1'],
                ["Inactive", '0'],
            ],
        }),
        displayField: 'l',
        valueField: 'v',
        emptyText: _('twilio.user.activefilter.empty'),
        mode: "local",
        triggerAction: "all",
        editable: false,
        selectOnFocus: false,
        preventRender: true,
        forceSelection: true,
        enableKeyEvents: true,
    });
    twilio.combo.UserActive.superclass.constructor.call(this, config);
}
Ext.extend(twilio.combo.UserActive, MODx.combo.ComboBox);
Ext.reg('twilio-combo-user-active', twilio.combo.UserActive);

twilio.combo.Use2fa = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        store: new Ext.data.SimpleStore({
            fields: ["l", "v"],
            data: [
                ["All", ""],
                ["Enabled", '1'],
                ["Disabled", '0'],
            ],
        }),
        displayField: 'l',
        valueField: 'v',
        emptyText: _('twilio.users.totp_value'),
        mode: "local",
        triggerAction: "all",
        editable: false,
        selectOnFocus: false,
        preventRender: true,
        forceSelection: true,
        enableKeyEvents: true,
    });
    twilio.combo.Use2fa.superclass.constructor.call(this, config);
}
Ext.extend(twilio.combo.Use2fa, MODx.combo.ComboBox);
Ext.reg('twilio-combo-use-2FA', twilio.combo.Use2fa);
