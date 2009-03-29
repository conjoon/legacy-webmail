/**
 * conjoon
 * (c) 2002-2009 siteartwork.de/conjoon.org
 * licensing@conjoon.org
 *
 * $Author$
 * $Id$
 * $Date$
 * $Revision$
 * $LastChangedDate$
 * $LastChangedBy$
 * $URL$
 */

Ext.namespace('com.conjoon.groupware.email');



com.conjoon.groupware.email.LatestEmailsPanel = function(config) {

    config = config || {};

    config.enableHdMenu = false;

    Ext.apply(this, config);

    Ext.ux.util.MessageBus.subscribe(
        'com.conjoon.groupware.email.view.onEmailLoad',
        this.onEmailItemLoad,
        this
    );



// ------------------------- set up buffered grid ------------------------------
    this.store = new Ext.ux.grid.livegrid.Store({
        bufferSize  : 100,
        autoLoad    : false,
        reader      : new Ext.ux.grid.livegrid.JsonReader({
                          root            : 'items',
                          totalProperty   : 'totalCount',
                          versionProperty : 'version',
                          id              : 'id'
                      },
                      com.conjoon.groupware.email.EmailItemRecord
                      ),
        sortInfo   : {field: 'id', direction: 'DESC'},
        baseParams : {
            minDate : Math.round(new Date().getTime()/1000)
        },
        listeners   : {
            remove : function (store, record, index) {
                Ext.ux.util.MessageBus.publish('com.conjoon.groupware.email.LatestEmailsPanel.store.remove', {
                    items : [record]
                });
            },
            bulkremove : function (store, items) {
                var records = [];
                for (var i = 0, len = items.length; i < len; i++) {
                    records.push(items[i][0]);
                }

                Ext.ux.util.MessageBus.publish('com.conjoon.groupware.email.LatestEmailsPanel.store.remove', {
                    items : records
                });
            },
            update : function (store, record, operation) {
                Ext.ux.util.MessageBus.publish('com.conjoon.groupware.email.LatestEmailsPanel.store.update', {
                    item      : record,
                    operation : operation
                });
            }
        },
        url : './groupware/email/get.email.items/format/json'
    });

    this.view = new Ext.ux.grid.livegrid.GridView({
        nearLimit : 25,
        loadMask  : {
            msg : com.conjoon.Gettext.gettext("Please wait...")
        },
        getRowClass : function(record, rowIndex, p, ds){
            if (record.data.isRead) {
                return 'com-conjoon-groupware-email-LatestEmailsPanel-itemRead';
            } else {
                return 'com-conjoon-groupware-email-LatestEmailsPanel-itemUnread';
            }
        }
    });

    this.selModel = new Ext.ux.grid.livegrid.RowSelectionModel({singleSelect:true});
// ------------------------- ^^ EO set up buffered grid ------------------------

    this.columns = [{
        header    : com.conjoon.Gettext.gettext("Subject"),
        width     : 160,
        sortable  : false,
        dataIndex : 'subject'
      },{
        header    : com.conjoon.Gettext.gettext("Sender"),
        width     : 160,
        sortable  : false,
        dataIndex : 'sender'
      }
    ];


    /**
     * Top toolbar
     * @param {Ext.Toolbar}
     */
    this.tbar = new Ext.Toolbar([
        new com.conjoon.groupware.email.FetchMenuButton()
    ]);


    com.conjoon.groupware.email.EmailGrid.superclass.constructor.call(this, {
        title          : com.conjoon.Gettext.gettext("Newest Emails"),
        iconCls        : 'com-conjoon-groupware-quickpanel-EmailIcon',
        loadMask       : {
            msg : com.conjoon.Gettext.gettext("Loading...")
        },
        autoScroll     : true//,
        //cls            : 'com-conjoon-groupware-email-EmailGrid'
    });

    com.conjoon.groupware.email.Letterman.on('load', this.newEmailsAvailable, this);


    this.on('contextmenu',    this.onContextClick, this);
    this.on('rowcontextmenu', this.onRowContextClick, this);
    this.on('beforedestroy',  this.onBeforeCmpDestroy, this);

    this.on('render', this.onPanelRender, this);

    com.conjoon.util.Registry.on('register', this.onRegister, this);

    com.conjoon.util.Registry.register('com.conjoon.groupware.email.QuickPanel', this);

    var preview       = com.conjoon.groupware.email.EmailPreview;
    this.emailPreview = preview;

    this.on('celldblclick',   this.onCellDblClick, this);
    this.on('cellclick',      this.onCellClick,    this, {buffer : 200});
    this.on('resize',         preview.hide.createDelegate(preview, [true]));
    this.on('beforecollapse', preview.hide.createDelegate(preview, [true, false]));
    this.on('contextmenu',    preview.hide.createDelegate(preview, [true]));

};

Ext.extend(com.conjoon.groupware.email.LatestEmailsPanel, Ext.grid.GridPanel, {

// -------- listeners
    cellClickActive : false,

    onCellClick : function(grid, rowIndex, columnIndex, eventObject)
    {
        if (this.cellClickActive) {
            this.cellClickActive = false;
            return;
        }
        this.emailPreview.show(grid, rowIndex, columnIndex, eventObject);
    },

    onCellDblClick : function(grid, rowIndex, columnIndex, eventObject)
    {
        this.cellClickActive = true;
        var emailItem = grid.getStore().getAt(rowIndex);

        var lr = this.emailPreview.getLastRecord();

        if (lr && lr.id === emailItem.id) {
            com.conjoon.groupware.email.EmailViewBaton.showEmail(lr, {
                autoLoad : false
            }, true);
        } else {
            com.conjoon.groupware.email.EmailViewBaton.showEmail(emailItem);
        }

        this.emailPreview.hide(true, false);
    },

    queue : null,

    onEmailsDeleted : function(records)
    {
        var st  = this.store;
        var rec;

        var prev   = com.conjoon.groupware.email.EmailPreview;
        var prevM  = prev.getActiveRecord;
        var prevId = null;
        for (var i = 0, max_i = records.length; i < max_i; i++) {

            rec = st.getById(records[i].id);

            if (rec) {
                st.remove(rec);
                prevId = prevM();
                if (prevId && prevId.id == rec.id) {
                    prev.hide(true, false);
                }
            }
        }

    },

    onBeforeCmpDestroy : function()
    {
        com.conjoon.util.Registry.unregister('com.conjoon.groupware.email.QuickPanel');
    },

    onEmailGridUpdate : function(store, record, operation)
    {
        if (operation == 'commit') {
            var myStore = this.store;
            var rec     = myStore.getById(record.id);
            var up      = 0;
            var data    = record.data;
            if (rec) {
                rec.data.groupwareEmailFoldersId = data.groupwareEmailFoldersId;
                rec.set('isRead', data.isRead);
            }
            myStore.suspendEvents();
            myStore.commitChanges();
            myStore.resumeEvents();
        }
    },

    onRegister : function(name, object)
    {
        if (name != 'com.conjoon.groupware.email.EmailPanel') {
            return;
        }

        this.onPanelRender();
    },

    onPanelRender : function()
    {
        var sub = com.conjoon.util.Registry.get('com.conjoon.groupware.email.EmailPanel');

        if (sub) {
            sub.gridPanel.store.un('update', this.onEmailGridUpdate, this);
            sub.gridPanel.store.on('update', this.onEmailGridUpdate, this);
            sub.on('emailsdeleted', this.onEmailsDeleted, this);
        }
    },

    /**
     * Subscribed to the message with the subject
     * com.conjoon.groupware.email.view.onEmailLoad.
     *
     * @param {String} subject
     * @param {Object} message
     */
    onEmailItemLoad : function(subject, message)
    {
        var emailRecord = message.emailRecord;

        var rec = this.store.getById(emailRecord.id);

        if (rec) {
            this.setItemsAsRead([rec], true);
        }
    },

//------------------------- Contextmenu related --------------------------------
    processQueue : function()
    {
        var ds = this.store;

        var record = this.queue.shift();
        if (!record) {
            this.queue = null;
            this.view.un('rowsinserted', this.processQueue, this);
            return;
        }

        //var index = ds.findInsertIndex(record);
        ds.insert.defer(0.0001, ds, [0, record]);
    },


    /**
     * Called by the letterman when new emails have arrived.
     * The letetrman will be responsible for
     *
     */
    newEmailsAvailable : function (store, records, options)
    {
        if (!this.queue) {
            this.queue = [];
        }

        this.view.un('rowsinserted', this.processQueue, this);
        this.view.on('rowsinserted', this.processQueue, this);

        for (var i = 0, max_i = records.length; i < max_i; i++) {
            this.queue.push(records[i].copy());
        }

        this.processQueue();
    },
//-------------------------------- Helpers -------------------------------------

    setItemsAsRead : function(records, read)
    {
        var requestArray = [];
        var change = false;
        var rec = null;
        for (var i = 0, max_i = records.length; i < max_i; i++) {
            rec = records[i];
            change = rec.get('isRead') != read;

            if (change) {
                records[i].set('isRead', read);
                requestArray.push({
                    id     : rec.id,
                    isRead : read
                });
            }
        }


        if (requestArray.length > 0) {
            Ext.Ajax.request({
                url: './groupware/email/set.email.flag/format/json',
                params: {
                    type : 'read',
                    json : Ext.encode(requestArray)
                }
            });
        }

        this.store.commitChanges();
    },


//------------------------- Contextmenu related --------------------------------

    onContextClick : function(e)
    {
        e.stopEvent();
    },

    onRowContextClick : function(grid, index, e)
    {
        var selModel = this.selModel;

        this.createContextMenu();

        e.stopEvent();

        if (!selModel.isSelected(index)) {
            selModel.selectRow(index, false);
        }

        var subItems  = this.menu.items;

        var ctxRecord = selModel.getSelected().data;
        subItems.get(0).setDisabled((ctxRecord.isRead == true));
        subItems.get(1).setDisabled(!(ctxRecord.isRead == true));

        this.menu.showAt(e.getXY());
    },

    createContextMenu : function()
    {
        if(!this.menu){
            this.menu = new Ext.menu.Menu({
                items: [{
                    text    : com.conjoon.Gettext.gettext("mark item as read"),
                    scope   : this,
                    handler : function(){this.setItemsAsRead(this.selModel.getSelections(), true);}
                  },{
                    text    : com.conjoon.Gettext.gettext("mark item as unread"),
                    scope   : this,
                    handler : function(){this.setItemsAsRead(this.selModel.getSelections(), false);}
                  }]
            });
        }
    }



});