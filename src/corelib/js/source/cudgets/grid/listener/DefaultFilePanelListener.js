/**
 * conjoon
 * (c) 2002-2010 siteartwork.de/conjoon.org
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

Ext.namespace('com.conjoon.cudgets.grid.listener');

/**
 * An  base class that provides the interface for listeners for
 * {com.conjoon.cudgets.grid.FilePanel}
 *
 * @author Thorsten Suckow-Homberg <ts@siteartwork.de>
 *
 * @class com.conjoon.cudgets.grid.listener.DefaultFilePanelListener
 *
 * @constructor
 */
com.conjoon.cudgets.grid.listener.DefaultFilePanelListener = function() {

};

com.conjoon.cudgets.grid.listener.DefaultFilePanelListener.prototype = {

    /**
     * @type {com.conjoon.cudgets.grid.FilePanel} panel The panel
     * this listener is bound to.
     */
    panel : null,

    /**
     * @type {String} clsId
     */
    clsId : '26882408-d373-429f-8fe7-28bf87726487',


// -------- api

    /**
     * Installs the listeners for the elements found in the panel.
     *
     * @param {com.conjoon.cudgets.grid.FilePanel} panel The panel
     * this listener is bound to.
     *
     * @packageprotected
     */
    init : function(panel)
    {
        if (this.panel) {
            return;
        }

        this.panel = panel;

        this.panel.on('rowcontextmenu', this.onRowContextClick, this);

        var cm = panel.getContextMenu();

        panel.mon(cm.getCancelItem(), 'click',
            this.onContextMenuCancelItemClick, this
        );

        panel.mon(cm.getRemoveItem(), 'click',
            this.onContextMenuRemoveItemClick, this
        );

        panel.mon(cm.getDownloadItem(), 'click',
            this.onContextMenuDownloadItemClick, this
        );

        panel.on('rowdblclick', this.onRowDblClick, this);

    },

// -------- helper

// ------- listeners

    /**
     * Listener for the grid's rowdblclick event.
     *
     * @param {Ext.grid.GridPanel} grid
     * @param {Number} rowIndex
     * @param {Ext.EventObject} eventObject
     *
     */
    onRowDblClick : function(grid, rowIndex, eventObject)
    {
        var rec = this.panel.getSelectionModel().getSelected();

        if (!rec || (rec && rec.get('location') != com.conjoon.cudgets.data
                                                   .FileRecord.LOCATION_REMOTE)) {
            return;
        }

        this.panel.fireEvent('downloadrequest', this.panel, rec);
    },

    /**
     * Listener for the click event of the "download" menu Item of the panels
     * context menu.
     *
     * @param {Ext.menu.Item} item
     */
    onContextMenuDownloadItemClick : function(item)
    {
        var rec = this.panel.getSelectionModel().getSelected();

        if (!rec || (rec && rec.get('location') != com.conjoon.cudgets.data
                                                   .FileRecord.LOCATION_REMOTE)) {
            return;
        }

        this.panel.fireEvent('downloadrequest', this.panel, rec);
    },

    /**
     * Listener for the click event of the "cancel" menu Item of the panels
     * context menu.
     *
     * @param {Ext.menu.Item} item
     */
    onContextMenuCancelItemClick : function(item)
    {
        var recs = [].concat(this.panel.getSelectionModel().getSelected());

        if (!recs || !recs.length) {
            return;
        }

        var fin  = [];

        var FileRecord = com.conjoon.cudgets.data.FileRecord;

        for (var i = 0, len = recs.length; i < len; i++) {
            if (recs[i].get('state') == FileRecord.STATE_UPLOADING) {
                this.panel.fireEvent('uploadcancel', this.panel, [recs[i]]);
            } else if (recs[i].get('state') == FileRecord.STATE_DOWNLOADING) {
                this.panel.fireEvent('downloadcancel', this.panel, [recs[i]]);
            }
        }
    },

    /**
     * Listener for the click event of the "remove" menu Item of the panels
     * context menu.
     *
     * @param {Ext.menu.Item} item
     */
    onContextMenuRemoveItemClick : function(item)
    {
        var recs = [].concat(this.panel.getSelectionModel().getSelected());

        if (!recs || !recs.length) {
            return;
        }

        var fin  = [];

        var FileRecord = com.conjoon.cudgets.data.FileRecord;

        for (var i = 0, len = recs.length; i < len; i++) {
            if (recs[i].get('state') == FileRecord.STATE_INVALID
                || recs[i].get('state') == FileRecord.LOCATION_REMOTE) {
                fin.push(recs[i]);
            }
        }

        if (fin.length == 0) {
            return;
        }

        this.panel.getStore().remove(fin);
        this.panel.fireEvent('recordremove', this.panel, fin);
    },

    /**
     * Listener for the rowcontextmenu event of the grid.
     *
     * @param {Ext.grid.GridPanel} grid
     * @param {Number} rowIndex
     * @param {Ext.EventObject} e
     *
     */
    onRowContextClick : function(grid, rowIndex, e)
    {
        e.stopEvent();

        var rec = this.panel.getStore().getAt(rowIndex);

        if (!rec) {
            return;
        }

        var selModel = this.panel.getSelectionModel();

        if (!selModel.isSelected(rowIndex)) {
            selModel.selectRow(rowIndex, false);
        }

        this.panel.showContextMenuForRecordAt(rec, e.getXY());
    }

};