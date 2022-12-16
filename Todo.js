  ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///


    buildLeftButton: function () {
        var me = this;
        this.leftButton = new Ext.Panel({
            id: 'desktop-left-panel',
            padding: '0px',
            border: false,
            width: 500,
            renderTo: Ext.getBody(),
            tbar: new Ext.Toolbar({
                cls: 'ux-start-menu',
                items: [
                    me.getLeftButton()
                ]
            })
        });
    },

    getLeftButton: function () {
        var me = this;
        return {
            id: 'todo-left-button',
            text: _t(`ToDo`),
            xtype: 'vetbutton',
            handler: function () {
                Ext.onReady(function () {
                    Ext.QuickTips.init();

                    var writer = new Ext.data.JsonWriter({
                        encode: true,
                        writeAllFields: true,
                        successProperty: 'success',

                    });

                    // var reader = new Ext.data.JsonReader({
                    //     // idProperty: 'id',
                    //     // root: 'rows',
                    //     // totalProperty: 'results',
                    //     // Ext.data.DataReader.messageProperty: "msg",  // The element within the response that provides a user-feedback message (optional)
                    //     successProperty: 'success',
                    //     fields: [
                    //         {name: 'id', mapping: 'id'},
                    //         {name: 'description', mapping: 'description'},
                    //         {name: 'status', mapping: 'status'},
                    //         {name: 'user_id', mapping: 'user_id'},
                    //     ]
                    // });

                    var storeJson = new Ext.data.JsonStore({
                        url: '/ajax_todo_get_all_records.php',
                        autoLoad: true,
                        autoDestroy: true,
                        storeId: 'storeJson',
                        root: 'records',
                        successProperty: 'success',
                        idProperty: 'id',
                        writer: writer,
                        // reader: reader,
                        fields: ['id', 'description', 'status', 'user_id'],

                        listeners: {
                            load: function (storeJson) {
                                storeJson.filter([{
                                    property: 'status',
                                    value: 'Не выполнено',
                                    anyMatch: true,
                                    caseSensitive: true
                                }]);
                            }
                        }
                    });

                    var formPanel = new Ext.FormPanel({
                        id: 'todo-form-panel',
                        labelWidth: 60,
                        title: 'New Case',
                        bodyStyle: 'padding:5px 5px 0',
                        width: 500,
                        defaults: {width: 400},
                        defaultType: 'textfield',

                        items: [{
                            fieldLabel: 'Case',
                            name: 'description',
                            allowBlank: false,
                        }, {
                            xtype: 'hidden',
                            name: 'user_id',
                            value: _CURRENT_USER
                        }, {
                            xtype: 'hidden',
                            name: 'action',
                            value: 'create'
                        }, {
                            xtype: 'hidden',
                            name: 'status',
                            value: 'Не выполнено'
                        }],

                        buttons: [{
                            text: 'Create',
                            handler: function () {
                                var formPanel = Ext.getCmp('todo-form-panel');
                                var values = formPanel.getForm().getFieldValues();

                                Ext.Ajax.request({
                                    url: '/ajax_todo_button_case.php',
                                    success: Ext.emptyFn,
                                    failure: function () {
                                        alert("Something went wrong");
                                    },
                                    params: {data: values}
                                });
                                // Перезагрузка данных(строк) в таблице
                                storeJson.reload();
                                // Сброс формы
                                formPanel.getForm().reset();
                            }
                        }]
                    });

                    var checkboxes = new Ext.grid.CheckboxSelectionModel({
                        listeners: {
                            selectionchange: function (sm) {
                                if (sm.getCount()) {
                                    generalWindow.items.get(0).removeButton.enable();
                                } else {
                                    generalWindow.items.get(0).removeButton.disable();
                                }
                            }
                        }
                    });

                    var columns = new Ext.grid.ColumnModel([
                        checkboxes,
                        {
                            header: 'process',
                            width: 30,
                            dataIndex: 'status',
                            xtype: 'actioncolumn',
                            items: [
                                {
                                    getClass: function (v, meta, rec) {
                                        if (rec.get('status') === 'Не выполнено') {
                                            return 'icon-flag-click-action-column-todo';
                                        } else {
                                            return 'icon-arrow-click-action-column-todo';
                                        }
                                    },
                                    handler: function (grid, rowIndex, colIndex) {
                                        var grPan = Ext.getCmp('grid-panel');
                                        var cellEl = Ext.fly(grPan.getView().getCell(rowIndex, colIndex));
                                        cellEl.removeClass("icon-arrow-click-action-column-todo");
                                        cellEl.addClass('icon-new-icon-click-action-column-todo');


                                        var records = storeJson.getAt(rowIndex);
                                        records.set('status', 'Mission complete');
                                        var writer = new Ext.data.JsonWriter(records);

                                        // console.log(writer);
                                        // records.commit();
                                       // storeJson.save();





                                        var idRecords = storeJson.getAt(rowIndex);
                                        var id = idRecords.id;

                                        var data = {id};

                                        data.action = 'statusEditOnIcon';
                                        data.status = 'Mission complete';

                                        Ext.defer(function () {
                                            Ext.Ajax.request({
                                                url: '/ajax_todo_button_case.php',
                                                scope: this,
                                                success: Ext.emptyFn,
                                                failure: function () {
                                                    alert("Something went wrong");
                                                },
                                                params: {
                                                    data: data,
                                                }
                                            });
                                            storeJson.reload();
                                        }, 2500);
                                    },
                                }
                            ]
                        },
                        {
                            description: 'description',
                            header: "description",
                            width: 30,
                            dataIndex: 'description'
                        }, {
                            status: 'status',
                            header: 'status',
                            width: 7,
                            dataIndex: 'status'
                        }
                    ]);

                    var generalWindow = new Ext.Window({
                        id: 'general',
                        layout: 'fit',
                        width: 800,
                        height: 500,
                        closeAction: 'hide',
                        plain: true,
                        items: [
                            new Ext.grid.GridPanel({
                                id: 'grid-panel',
                                store: storeJson,
                                sm: checkboxes,
                                cm: columns,

                                viewConfig: {forceFit: true},

                                columnLines: true,
                                buttons: [{
                                    text: 'Close',
                                    handler: function () {
                                        generalWindow.hide();
                                    }
                                }],
                                tbar: [{
                                    text: 'Add',
                                    tooltip: 'Add a new case',
                                    handler: function () {
                                        var win = new Ext.Window({
                                            layout: 'fit',
                                            width: 500,
                                            height: 280,
                                            closeAction: 'hide',
                                            plain: true,
                                            items:
                                                [
                                                    formPanel
                                                ]
                                        });
                                        win.show();
                                    },
                                }, '|', {
                                    text: 'Done',
                                    tooltip: 'Done',
                                    ref: '../removeButton',
                                    handler: function () {
                                        var genGrid = Ext.getCmp('grid-panel');

                                        // записи из чекбокса выбранные
                                        var records = checkboxes.getSelections();

                                        // установить стили
                                        Ext.each(records, function (rec) {
                                            var index = genGrid.getStore().indexOf(rec);
                                            var rowEl = Ext.fly(genGrid.getView().getRow(index));
                                            rowEl.addClass('row-hidden-todo');
                                        });

                                        Ext.defer(function () {
                                            var idSelectedItems = checkboxes.getSelections();
                                            var data = {
                                                'ids': idSelectedItems.map((el) => ({['id']: el.id}))
                                            };
                                            data.action = 'statusEditOnCheckbox';
                                            data.status = 'Mission complete';

                                            Ext.Ajax.request({
                                                url: '/ajax_todo_button_case.php',
                                                scope: this,
                                                success: Ext.emptyFn,
                                                failure: function () {
                                                    alert("Something went wrong");
                                                },
                                                params: {
                                                    data: data,
                                                }
                                            });
                                            storeJson.reload();
                                        }, 2500);
                                    }
                                }, '|', {
                                    text: 'Remove',
                                    tooltip: 'Remove the selected item',
                                    disabled: true,
                                    ref: '../removeButton',
                                    handler: function () {
                                        var idSelectedItems = checkboxes.getSelections();
                                        var data = {
                                            'ids': idSelectedItems.map((el) => ({['id']: el.id}))
                                        };
                                        data.action = 'delete';

                                        Ext.Ajax.request({
                                            url: '/ajax_todo_button_case.php',
                                            scope: this,
                                            success: Ext.emptyFn,
                                            failure: function () {
                                                alert("Something went wrong");
                                            },
                                            params: {
                                                data: data,
                                            }
                                        });
                                        storeJson.reload();
                                    },
                                }]
                            })
                        ]
                    });
                    generalWindow.show();
                });
            }
        };
    },
    ///
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////
