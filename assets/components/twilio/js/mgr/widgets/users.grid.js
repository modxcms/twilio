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
        url: twilio.config.connector_url,
        id: 'twilio-grid-users',
        baseParams: {
            action: 'users/getlist'
        }
        ,fields: ['id',
            'username',
            'active',
            'add_groups',
            'primary_group',
            'primary_group_name',
            'primary_group_role',
            'profile_blocked',
            'profile_comment',
            'profile_email',
            'profile_fullname',
            'profile_lastlogin',
            'totp_value',
            'totp_status'
        ]
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
            header: _('active')
            ,dataIndex: 'active'
            ,width: 100
            ,sortable: true
            ,hidden: true
            ,renderer: this.rendYesNo
        },{
            header: _('user_block')
            ,dataIndex: 'profile_blocked'
            ,width: 100
            ,sortable: true
            ,hidden: true
            ,renderer: this.rendYesNo
        },{
            header: _('user_full_name')
            ,dataIndex: 'profile_fullname'
            ,width: 200
            ,sortable: true
            ,hidden: false
        },{
            header: _('email')
            ,dataIndex: 'profile_email'
            ,width: 200
            ,sortable: true
            ,hidden: false
        },{
            header: _('primary_group')
            ,dataIndex: 'primary_group_name'
            ,width: 200
            ,sortable: true
            ,hidden: false
            ,renderer: function(value, metaData, record, rowIndex, colIndex, store) {
                return value + ' (' + record.data.primary_group_role + ')';
            }
        },{
            header: _('twilio.users.additional_groups')
            ,dataIndex: 'add_groups'
            ,width: 200
            ,sortable: true
            ,hidden: false
            ,renderer: function(value, metaData, record, rowIndex, colIndex, store) {
                var groupNames = []
                Ext.each(value, function (group, index) {
                    groupNames.push(group.name + ' (' + group.role + ')');
                });
                return groupNames.join(', ');
            }
        },{
            header: _('role')
            ,dataIndex: 'primary_group_role'
            ,width: 200
            ,sortable: false
            ,hidden: true
        },{
            header: _('comment')
            ,dataIndex: 'profile_comment'
            ,width: 200
            ,sortable: false
            ,hidden: true
        },{
            header: _('user_prevlogin')
            ,dataIndex: 'profile_lastlogin'
            ,width: 200
            ,sortable: false
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
    filterSearch: function (comp, search) {
        var s = this.getStore();
        s.baseParams[comp.filterName] = search;
        this.getBottomToolbar().changePage(1);
    },
    filterCombo: function (combo, record) {
        var s = this.getStore();
        s.baseParams[combo.filterName] = record.data[combo.valueField];
        this.getBottomToolbar().changePage(1);
    },
    exportFilters: function (comp, search) {
        var s = this.getStore();
        var filters = "export=true&HTTP_MODAUTH=" + MODx.siteId;
        Object.keys(s.baseParams).forEach((key) => {
            filters += "&" + key + "=" + s.baseParams[key];
        });
        window.location = this.config.url + "?" + filters;
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
            },{
                text: _("twilio.users.export"),
                handler: this.exportFilters,
                scope: this,
            },'->',{
                xtype: 'twilio-combo-use-2FA',
                name: '2fa',
                scope: this,
                filterName: "2fa",
                listeners: {
                    select: this.filterCombo,
                    scope: this
                }
            },{
                xtype: 'twilio-combo-user-active',
                name: 'active',
                scope: this,
                filterName: "active",
                listeners: {
                    select: this.filterCombo,
                    scope: this
                }

            },{
                xtype: 'textfield',
                emptyText: _('search_ellipsis'),
                id: 'twilio-filter-search',
                filterName: "search",
                listeners: {
                    change: this.filterSearch,
                    scope: this,
                    render: {
                        fn: function (cmp) {
                            new Ext.KeyMap(cmp.getEl(), {
                                key: Ext.EventObject.ENTER,
                                fn: this.blur,
                                scope: cmp,
                            });
                        },
                        scope: this,
                    },
                }
            }]);

        return tbar;
    },

});
Ext.reg('twilio-grid-users', twilio.grid.Users);
