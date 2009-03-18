/**
 * Tine 2.0
 * 
 * @package     Admin
 * @subpackage  User
 * @license     http://www.gnu.org/licenses/agpl.html AGPL Version 3
 * @author      Cornelius Weiss <c.weiss@metaways.de>
 * @copyright   Copyright (c) 2009 Metaways Infosystems GmbH (http://www.metaways.de)
 * @version     $Id$
 *
 */
 
Ext.namespace('Tine.Admin', 'Tine.Admin.Users');

Tine.Admin.Users.EditDialog  = Ext.extend(Tine.widgets.dialog.EditDialog, {
    
    /**
     * @private
     */
    windowNamePrefix: 'userEditWindow_',
    appName: 'Admin',
    recordClass: Tine.Admin.Model.User,
    recordProxy: Tine.Admin.userBackend,
    loadRecord: false,
    
    initComponent: function() {
        var accountBackend = Tine.Tinebase.registry.get('accountBackend');
        this.ldapBackend = (accountBackend == 'Ldap');

        Tine.Admin.Users.EditDialog.superclass.initComponent.call(this);
    },

    /**
     * overwrite update toolbars function (we don't have record grants yet)
     */
    updateToolbars: function() {

    },
/*
    onRecordLoad: function() {
        // make shure grants grid is initialised
        this.getGrantsGrid();
        
        var grants = this.record.get('grants') || [];
        this.grantsStore.loadData({results: grants});
        Tine.Timetracker.TimeaccountEditDialog.superclass.onRecordLoad.call(this);
        
    },
    
    onRecordUpdate: function() {
        Tine.Timetracker.TimeaccountEditDialog.superclass.onRecordUpdate.call(this);
        this.record.set('grants', '');
        
        var grants = [];
        this.grantsStore.each(function(_record){
            grants.push(_record.data);
        });
        
        this.record.set('grants', grants);
    },
*/

    getFormItems: function() {
        return {
            xtype: 'tabpanel',
            border: false,
            plain:true,
            activeTab: 0,
            border: false,
            items:[{               
                title: this.app.i18n._('Account'),
                autoScroll: true,
                border: false,
                frame: true,
                layout: 'border',
                items: [{
                    region: 'center',
                    xtype: 'columnform',
                    labelAlign: 'top',
                    formDefaults: {
                        xtype:'textfield',
                        anchor: '100%',
                        labelSeparator: '',
                        columnWidth: .333
                    },
                    items: [[{
                            fieldLabel: this.app.i18n._('First Name'),
                            name: 'accountFirstName',
                            columnWidth: .666
                        }, {
                            xtype: 'combo',
                            fieldLabel: this.app.i18n._('Status'),
                            name: 'accountStatus',
                            mode: 'local',
                            triggerAction: 'all',
                            allowBlank: false,
                            editable: false,
                            store: [['enabled', this.app.i18n._('enabled')],['disabled', this.app.i18n._('disabled')]],
                            disabled: this.ldapBackend
                        }], [{
                            fieldLabel: this.app.i18n._('Last Name'),
                            name: 'accountLastName',
                            allowBlank: false,
                            columnWidth: .666
                        }, new Ext.ux.form.ClearableDateField({ 
                            fieldLabel: this.app.i18n._('Expires'),
                            name: 'accountExpires',
                            emptyText: this.app.i18n._('never')
                        })], [{
                            fieldLabel: this.app.i18n._('Login Name'),
                            name: 'accountLoginName',
                            allowBlank: false,
                            columnWidth: .666
                        }, {
                            xtype: 'datetimefield',
                            fieldLabel: this.app.i18n._('Last login at'),
                            name: 'accountLastLogin',
                            emptyText: this.ldapBackend ? this.app.i18n._("don't know") : this.app.i18n._('never logged in'),
                            hideTrigger: true,
                            readOnly: true,
                        }], [{
                            fieldLabel: this.app.i18n._('Password'),
                            name: 'accountPassword',
                            inputType: 'password',
                            emptyText: this.app.i18n._('no password set'),
                            columnWidth: .666
                        }, {
                            xtype: 'textfield',
                            fieldLabel: this.app.i18n._('Last login from'),
                            name: 'accountLastLoginfrom',
                            emptyText: this.ldapBackend ? this.app.i18n._("don't know") : this.app.i18n._('never logged in'),
                            readOnly: true,
                        }], [{
                            fieldLabel: this.app.i18n._('Password again'),
                            name: 'accountPassword2',
                            inputType: 'password',
                            emptyText: this.app.i18n._('no password set'),
                            columnWidth: .666
                        }, {
                            xtype: 'datetimefield',
                            fieldLabel: this.app.i18n._('Password set'),
                            name: 'accountLastPasswordChange',
                            emptyText: this.app.i18n._('never'),
                            hideTrigger: true,
                            readOnly: true
                        }], [new Tine.widgets.group.selectionComboBox({
                            fieldLabel: this.app.i18n._('Primary group'),
                            name: 'accountPrimaryGroup',
                            displayField:'name',
                            valueField:'id',
                            columnWidth: .666
                        })], [{
                            vtype: 'email',
                            fieldLabel: this.app.i18n._('Emailaddress'),
                            name: 'accountEmailAddress',
                            columnWidth: .666
                        }], [{
                            fieldLabel: this.app.i18n._('Home Directory'),
                            name: 'accountHomeDirectory',
                            columnWidth: .666
                        }], [{
                            fieldLabel: this.app.i18n._('Login Shell'),
                            name: 'accountLoginShell',
                            columnWidth: .666
                        }] ] 
                }]
            }]
        };
    },
 });

/**
 * User edit popup
 */
Tine.Admin.Users.EditDialog.openWindow = function (config) {
    var id = (config.record && config.record.id) ? config.record.id : 0;
    var window = Tine.WindowFactory.getWindow({
        width: 400,
        height: 500,
        name: Tine.Admin.Users.EditDialog.prototype.windowNamePrefix + id,
        contentPanelConstructor: 'Tine.Admin.Users.EditDialog',
        contentPanelConstructorConfig: config
    });
    return window;
};
