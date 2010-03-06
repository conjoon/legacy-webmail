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

Ext.namespace('com.conjoon.cudgets.localCache');

/**
 * An abstract class to provide concrete Application Cache implementations for use with
 * com.conjoon.cudgets.localCache.Api
 *
 * @author Thorsten Suckow-Homberg <ts@siteartwork.de>
 *
 * @class com.conjoon.cudgets.localCache.Adapter
 * @extends Ext.util.Observable
 */

com.conjoon.cudgets.localCache.Adapter = function() {

    this.addEvents(
        /**
         * @event beforeclear
         * Gets fired before an attempt is made to clear the local cache.
         * @param {com.conjoon.cudgets.localCache.Adapter}
         */
        'beforeclear',
        /**
         * @event clearsuccess
         * gets fired when clearing the cache was successfull.
         * @param {com.conjoon.cudgets.localCache.Adapter}
         */
        'clearsuccess',
        /**
         * @event clearfailure
         * Gets fired when clearing the cache was not successfull.
         * @param {com.conjoon.cudgets.localCache.Adapter}
         */
        'clearfailure'
    );

    com.conjoon.cudgets.localCache.Adapter.superclass.constructor.call(this);
};

Ext.extend(com.conjoon.cudgets.localCache.Adapter, Ext.util.Observable, {

    /**
     * Returns true if the cache the concrete implementation of this class is
     * available, otherwise false
     *
     * @return {Boolean}
     *
     * @abstract
     */
    isCacheAvailable : Ext.emptyFn,

    /**
     * Returns the textual representation for the local cache the concrete
     * implementation of this class represents.
     *
     * @return {String}
     *
     * @abstract
     */
    getCacheType : Ext.emptyFn,

    /**
     * Clears the local cache. Concrete implementations of this class are
     * advised to properly fire the "beforeclear" and "clearsuccess" or
     * "clearfailure" event.
     *
     * @abstract
     */
    clearCache : Ext.emptyFn


});