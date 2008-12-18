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

Ext.namespace('com.conjoon.groupware.feeds');

/**
 * @class com.conjoon.groupware.feeds.FeedRunner
 *
 */
com.conjoon.groupware.feeds.FeedRunner = function(){

    var commitId = Ext.data.Record.COMMIT;

    var store = new Ext.data.Store({
        autoLoad   : false,
        reader     : new Ext.data.JsonReader({
                          root: 'items',
                          id : 'id'
                      }, com.conjoon.groupware.feeds.ItemRecord),
        baseParams  : {
            removeold : false,
            timeout   : com.conjoon.groupware.feeds.FeedStore.getDefaultTimeOut()
        },
        proxy : new Ext.data.HttpProxy({
            url      : './groupware/feeds/get.feed.items/format/json',
            timeout  : com.conjoon.groupware.feeds.FeedStore.getDefaultTimeOut()
        })
    });

    var firstTimeLoaded = false;

    var task = null;

    var runnable = false;

    var updateInterval = Number.MAX_VALUE;

    var defaultUpdateInterval = 3600;

    var onStoreLoadException = function(proxy, options, response, jsError)
    {
        com.conjoon.groupware.ResponseInspector.handleFailure(response);
    };

    var onStoreLoad = function(store, records, options)
    {
        if (!records || (records && !records.length)) {
            return;
        }

        for (var i = 0, len = records.length; i < len; i++) {
            feedStore.addSorted(records[i].copy());
        }

        store.removeAll();
        if (len > 0) {
            notifyUser(len);
        }
    };

    var onStoreChange = function(store, record, operation)
    {
        if (operation && (typeof(operation) == 'string') && operation != commitId) {
            return;
        }

        stopRunning();

        var recs = store.getRange();

        for (var i = 0, len = recs.length; i < len; i++) {
            updateInterval = Math.min(recs[i].get('updateInterval'), updateInterval);
        }

        run();
    };

    var stopRunning = function()
    {
        runnable = false;
        if (task) {
            Ext.TaskMgr.stop(task);
        }
    };

    var run = function()
    {
        task = {
            run      : updateFeeds,
            interval : (updateInterval <= 0 ?
                        defaultUpdateInterval :
                        updateInterval)*1000
        }
        Ext.TaskMgr.start(task);
    };

    var updateFeeds = function()
    {
        if (!runnable) {
            runnable = true;
            return;
        }

        if (_reception.isLocked()) {
            return;
        }

        if (!firstTimeLoaded) {
            store.load();
            firstTimeLoaded = true;
        } else {
            store.reload();
        }

    };

    /**
     *
     * @param {Number} feedCount any value > 0
     */
    var notifyUser = function(feedCount)
    {
        var text = String.format(
            com.conjoon.Gettext.ngettext("There is one new feed entry available", "There are {0} new feed entries available", feedCount),
            feedCount
        );

        new Ext.ux.ToastWindow({
            title   : com.conjoon.Gettext.ngettext("New feed entry available", "New feed entries available", feedCount),
            html    : text
        }).show(document);

    };

    // leave this here since listener only works if observer function gets defined before
    // (stopRunning, onStoreLoad)
    var feedStore = com.conjoon.groupware.feeds.FeedStore.getInstance();
    feedStore.on('beforeload', stopRunning, com.conjoon.groupware.feeds.FeedRunner);
    store.on('load', onStoreLoad, com.conjoon.groupware.feeds.FeedRunner);
    store.on('loadexception', onStoreLoadException, com.conjoon.groupware.feeds.FeedRunner);

    var _reception = com.conjoon.groupware.Reception;

    return {


        getListener : function()
        {
            return {
                add    : onStoreChange,
                remove : onStoreChange,
                update : onStoreChange,
                load   : onStoreChange
            };
        }

    };

}();