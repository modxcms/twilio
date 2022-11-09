twilio.grid.Users = function (config) {
    config = config || {};
    this.sm = new Ext.grid.CheckboxSelectionModel({
        listeners: {
            rowselect: {
                fn: function (sm, rowIndex, record) {
                    this.rememberRow(record);
                }, scope: this
            },
            rowdeselect: {
                fn: function (sm, rowIndex, record) {
                    this.forgotRow(record);
                }
                ,scope: this
            }
        }
    });
    Ext.applyIf(config,{
        url: MODx.config.connector_url,
        id: 'twilio-grid-users',
        baseParams: {
            action: 'users/getlist'
        }
        ,fields: ['id','username', 'profile_fullname', 'profile_email', 'totp_value', 'totp_status']
        ,sm: this.sm
        ,autoHeight: true
        ,paging: true
        ,remoteSort: true
        ,columns: [
            this.sm,
            {
                header: _('id')
                ,dataIndex: 'id'
                ,width: 70
                ,hidden: true
                ,sortable: true
        },{
            header: _('twilio.users.username')
            ,dataIndex: 'username'
            ,width: 100
            ,sortable: true
            ,hidden: false
        },{
            header: _('twilio.users.fullname')
            ,dataIndex: 'profile_fullname'
            ,width: 200
            ,sortable: true
            ,hidden: false
        },{
            header: _('twilio.users.email')
            ,dataIndex: 'profile_email'
            ,width: 200
            ,sortable: true
            ,hidden: false
        }, {
            header: _('twilio.users.totp_value')
            ,dataIndex: 'totp_value'
            ,width: 200
            ,sortable: true
            ,hidden: false
            ,renderer: this.rendYesNo
        }, {
            header: _('twilio.users.totp_status')
            ,dataIndex: 'totp_status'
            ,width: 200
            ,sortable: false
            ,hidden: false
            ,renderer: function (value, metaData, record, rowIndex, colIndex, store) {
                return _('twilio.users.totp_status_' + value);
            }
        }
        ]
        ,tbar: this.getTbar(config)
    });
    twilio.grid.Users.superclass.constructor.call(this,config);
}
Ext.extend(twilio.grid.Users, MODx.grid.Grid, {
    selectedRecords: [],
    getMenu: function () {
        var m = [];
        var set = this.menu.record.totp_status !== 'not_set' && this.menu.record.totp_status !== null;
        if (this.menu.record.totp_value !== 1) {
            m.push({
                text: _('twilio.users.enable_totp'),
                handler: this.enableTotp,
                single: true,
            });
            if (this.menu.record.totp_status !== null) {
                m.push({
                    text: _('twilio.users.clear_totp'),
                    handler: this.clearTotp,
                    single: true,
                });
            }
        } else {
            m.push({
                text: set ?
                    _('twilio.users.reset_totp') : _('twilio.users.create_totp'),
                handler: this.createTotp,
                single: true,
            });
            m.push({
                text: _('twilio.users.email_totp'),
                handler: this.emailTotp,
                single: true,
            });
            m.push({
                text: _('twilio.users.disable_totp'),
                handler: this.disableTotp,
                single: true,
            });
        }
        return m;
    },

    enableTotp: function (btn, e) {
        var btnConfig = btn.initialConfig.options || btn.initialConfig;
        var grid = Ext.getCmp('twilio-grid-users');
        var ids = btnConfig.single ? [this.menu.record.id] : grid.getSelectedAsList();

        if (ids.length === 0) {
            return false;
        }

        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'totp/status',
                user: ids,
                status: 1,
            },
            listeners: {
                success: {
                    fn: function () {
                        grid.refresh();
                    }, scope: this
                }
            }
        });
    },

    disableTotp: function (btn, e) {
        var btnConfig = btn.initialConfig.options || btn.initialConfig;
        var grid = Ext.getCmp('twilio-grid-users');
        var ids = btnConfig.single ? [this.menu.record.id] : grid.getSelectedAsList();

        if (ids.length === 0) {
            return false;
        }

        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'totp/status',
                user: ids,
                status: 0,
            },
            listeners: {
                success: {
                    fn: function () {
                        grid.refresh();
                    }, scope: this
                }
            }
        });
    },

    clearTotp: function (btn, e) {
        var r = this.menu.record;
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'totp/clear',
                user: r.id,
            },
            listeners: {
                success: {
                    fn: function () {
                        grid.refresh();
                    }, scope: this
                }
            }
        });
    },

    createTotp: function (btn, e) {
        var btnConfig = btn.initialConfig.options || btn.initialConfig;
        var grid = Ext.getCmp('twilio-grid-users');
        var ids = btnConfig.single ? [this.menu.record.id] : grid.getSelectedAsList();

        if (ids.length === 0) {
            return false;
        }

        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'totp/create',
                user: ids,
            },
            listeners: {
                success: {
                    fn: function () {
                        grid.refresh();
                    }, scope: this
                }
            }
        });
    },

    emailTotp: function (btn, e) {
        var btnConfig = btn.initialConfig.options || btn.initialConfig;
        var grid = Ext.getCmp('twilio-grid-users');
        var ids = btnConfig.single ? [this.menu.record.id] : grid.getSelectedAsList();

        if (ids.length === 0) {
            return false;
        }

        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'totp/email',
                user: ids,
            },
            listeners: {
                success: {
                    fn: function () {
                        grid.refresh();
                    }, scope: this
                }
            }
        });
    },

    selectRows: function (ids) {
        Ext.each(ids, function (id) {
            if (this.selectedRecords.indexOf(id) === -1) {
                this.selectedRecords.push(id);
                this.enableTbarButtons();

                var indexOfId = this.store.indexOfId(id);
                if (indexOfId !== -1) {
                    this.selModel.selectRow(indexOfId, true);
                }
            }
        },this);
    },

    unselectRows: function (ids) {
        Ext.each(ids, function (id) {
            this.selectedRecords.remove(id);
            if (this.selectedRecords.length === 0) {
                this.disableTbarButtons();
            }

            var indexOfId = this.store.indexOfId(id);
            if (indexOfId !== -1) {
                this.selModel.deselectRow(indexOfId);
            }
        },this);
    },

    rememberRow: function (record) {
        if (this.selectedRecords.indexOf(record.id) === -1) {
            this.selectedRecords.push(record.id);
            this.enableTbarButtons();
        }
    },

    forgotRow: function (record) {
        this.selectedRecords.remove(record.id);
        if (this.selectedRecords.length === 0) {
            this.disableTbarButtons();
        }
    },

    disableTbarButtons: function () {
        Ext.getCmp('twilio-all_changes-with-selected').disable();
    },

    enableTbarButtons: function () {
        Ext.getCmp('twilio-all_changes-with-selected').enable();
    },

    getSelectedAsList: function () {
        return this.selectedRecords.join();
    },

    search: function (tf, newValue, oldValue) {
        var nv = newValue || tf;
        this.getStore().baseParams.search = Ext.isEmpty(nv) || Ext.isObject(nv) ? '' : nv;
        this.getBottomToolbar().changePage(1);
        return true;
    },
    getTbar: function (config) {
        var tbar = [];

        tbar.push([
            {
                text: _('twilio.users.with-selected'),
                id: 'twilio-all_changes-with-selected',
                disabled: true,
                menu: [
                    {
                        text: _('twilio.users.reset_totp'),
                        single: false,
                        config: config,
                        handler: this.createTotp,
                },{
                    text: _('twilio.users.email_totp'),
                    single: false,
                    config: config,
                    handler: this.emailTotp,
                },{
                    text: _('twilio.users.enable_totp'),
                    single: false,
                    config: config,
                    handler: this.enableTotp,
                },{
                    text: _('twilio.users.disable_totp'),
                    single: false,
                    config: config,
                    handler: this.disableTotp,
                }
                ]
            },'->',{
                xtype: 'textfield',
                emptyText: _('search_ellipsis'),
                id: 'twilio-filter-search',
                listeners: {
                    change: {
                        fn: this.search,
                        scope: this
                    },
                    render: {
                        fn: function (cmp) {
                            new Ext.KeyMap(cmp.getEl(), {
                                key: Ext.EventObject.ENTER,
                                fn: function () {
                                    this.blur();
                                    return true;
                                },
                                scope: cmp
                            });
                        },
                        scope:this
                    }
                }
            }]);

        return tbar;
    },

});
Ext.reg('twilio-grid-users', twilio.grid.Users);
