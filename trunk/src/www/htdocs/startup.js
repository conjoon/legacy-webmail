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

Ext.onReady(function(){

    var preLoader         = com.conjoon.util.PreLoader
    var groupware         = com.conjoon.groupware;
    var emailAccountStore = groupware.email.AccountStore.getInstance();
    var feedsAccountStore = groupware.feeds.AccountStore.getInstance();
    var registryStore     = groupware.Registry.getStore();
    var feedsFeedStore    = groupware.feeds.FeedStore.getInstance();
    var reception         = groupware.Reception

    var loadingCont = document.getElementById(
        'com.conjoon.groupware.Startup.loadingCont'
    );

    var loadingInd = null;

    var _load = function(store) {
        _updateIndicator(store.storeId);
    };

    var _loadException = function(store) {
        _updateFailIndicator(store.storeId);
    };

    var _updateFailIndicator = function(id) {
        var div = document.getElementById(id);
        if (!div) {
            return;
        }
        Ext.fly(div).addClass('fail');
        div.innerHTML = div.innerHTML + '&nbsp;' + com.conjoon.Gettext.gettext("Failed :(");
    };

    var _updateIndicator = function(id) {
        var div = document.getElementById(id);
        if (!div) {
            return;
        }
        Ext.fly(div).addClass('done');
        div.innerHTML = div.innerHTML + '&nbsp;' + com.conjoon.Gettext.gettext("Done!");
    };

    var _appendIndicator = function(msg, id) {
        if (!loadingInd) {
            loadingInd = document.createElement('div');
            loadingInd.className = 'loading';
        }

        var cn       = loadingInd.cloneNode(true);
        cn.innerHTML = msg;
        cn.id        = id;
        loadingCont.appendChild(cn);
    };

    var _beforeLoad = function(store) {

        var msg = "";

        switch (store) {
            case emailAccountStore:
                msg = com.conjoon.Gettext.gettext("Loading Email accounts...");
            break;

            case feedsAccountStore:
                msg = com.conjoon.Gettext.gettext("Loading Feed accounts...");
            break;

            case registryStore:
                msg = com.conjoon.Gettext.gettext("Loading Registry...");
            break;

            case feedsFeedStore:
                msg = com.conjoon.Gettext.gettext("Loading Feeds...");
            break;
        }

        _appendIndicator(msg, store.storeId);
    };

    // add listeners
    preLoader.on('beforestoreload',    _beforeLoad);
    preLoader.on('storeload',          _load);
    preLoader.on('storeloadexception', _loadException);

    reception.onBeforeUserLoad(function() {
        _appendIndicator(
            com.conjoon.Gettext.gettext("Loading User..."),
            'reception-id'
        );
    });
    reception.onUserLoad(function(){
        _updateIndicator('reception-id');
    });
    reception.onUserLoadFailure(function(){
        _updateFailIndicator('reception-id');
    });

    Ext.QuickTips.init();

    Ext.getBody().on('contextmenu', function(e){
        var t = e.getTarget().tagName;
        if (t != 'INPUT' && t != 'TEXTAREA') {
            e.stopEvent();
        }
    });

    preLoader.addStore(emailAccountStore);
    preLoader.addStore(feedsAccountStore);
    preLoader.addStore(registryStore);
    preLoader.addStore(feedsFeedStore, {
        ignoreLoadException : true,
        loadAfterStore      : feedsAccountStore
    });

    preLoader.on('load', function() {

        preLoader.un('beforestoreload',    _beforeLoad);
        preLoader.un('storeload',          _load);
        preLoader.un('storeloadexception', _loadException);

        reception.removeAllListeners();

        groupware.email.Letterman.wakeup();

        com.conjoon.util.Registry.register(
            'com.conjoon.groupware.Workbench',
            new groupware.Workbench()
        );

        (function(){
            Ext.fly(document.getElementById('DOM:com.conjoon.groupware.Startup')).fadeOut({
                endOpacity : 0, //can be any value between 0 and 1 (e.g. .5)
                easing     : 'easeOut',
                duration   : .5,
                remove     : true,
                useDisplay : false
            });

            com.conjoon.SystemMessageManager.setContext(groupware.Registry.get(
                '/client/environment/device'
            ));

            Ext.ux.util.MessageBus.publish('com.conjoon.groupware.ready');

        }).defer(100);
    });

    reception.init(true);
    reception.onUserLoad(function(){
        preLoader.load();
    });

    /**
     * Shows a confirm dialog when the user wants to leave conjoon.
     */
    window.onbeforeunload = function (evt) {
        var message = com.conjoon.Gettext.gettext("conjoon\nAre you sure you want to exit your current session?");
        if (typeof evt == "undefined") {
            evt = window.event;
        }
        if (evt) {
          evt.returnValue = message;
        }
        return message;
    };


});