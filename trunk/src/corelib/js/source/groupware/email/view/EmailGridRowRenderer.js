/**
 * intrabuild
 * (c) 2002-2008 siteartwork.de/MindPatterns
 * license@siteartwork.de
 *
 * $Author: T. Suckow $
 * $Id: EmailGrid.js 172 2008-09-23 20:29:14Z T. Suckow $
 * $Date: 2008-09-23 22:29:14 +0200 (Di, 23 Sep 2008) $
 * $Revision: 172 $
 * $LastChangedDate: 2008-09-23 22:29:14 +0200 (Di, 23 Sep 2008) $
 * $LastChangedBy: T. Suckow $
 * $URL: file:///F:/svn_repository/intrabuild_rep/trunk/src/corelib/js/source/groupware/email/EmailGrid.js $
 */

Ext.namespace('de.intrabuild.groupware.email.view');

/**
 *
 * @class de.intrabuild.groupware.email.view.EmailGridRowRenderer
 * @singleton
 */
de.intrabuild.groupware.email.view.EmailGridRowRenderer = function()
{
    var _idCache = [];

    // shorthand to Ext.data.Record.COMMIT
    var _commit = Ext.data.Record.COMMIT;

    var _dateFormat = 'd.m.Y';
    var _timeFormat = 'H:i';


    /**
     * Called when a manual request for new emails from the Letterman has been made.
     * Will reset _idCache to an empty array.
     *
     * @param {String} subject
     * @param {Object}
     *
     */
    var _clearCache = function(subject, message)
    {
        Ext.ux.util.MessageBus.publish('de.intrabuild.groupware.email.LatestEmailCache.clear', {
            itemIds : _idCache.splice(0, _idCache.length)
        });
        _idCache = [];
    };

    /**
     * Called when an item in the email grid has been removed or updated.
     * Will remove the id of the item found in the message out of
     * _idCache.
     *
     * @param {String} subject
     * @param {Object}
     *
     */
    var _onEmailItemChange = function(subject, message)
    {
        if (subject == 'de.intrabuild.groupware.email.EmailGrid.store.update' && message.operation != _commit) {
            return;
        }

        _idCache.remove(message.item.id);
    };

    /**
     * Called when the letterman has fetched new emails. Will store the
     * ids of the new items in a numeric array.
     *
     * @param {String} subject
     * @param {Object
     *
     */
    var _onLettermanLoad = function(subject, message)
    {
        if (message.total == 0) {
            return;
        }

        var recs = message.items;
        var id   = null;
        for (var i = 0, len = message.total; i < len; i++) {
            id = recs[i].id;
            if (_idCache.indexOf(recs[i].id) == -1) {
                _idCache.push(id);
            }
        }

    };

    /**
     * The renderer subscribes to the load/peekIntoInbox message published by the Letterman
     */
    Ext.ux.util.MessageBus.subscribe(
        'de.intrabuild.groupware.email.Letterman.load',
        _onLettermanLoad
    );
    Ext.ux.util.MessageBus.subscribe(
        'de.intrabuild.groupware.email.Letterman.peekIntoInbox',
        _clearCache
    );

    /**
     * The renderer subscribes to the remove/update message published by the email grid
     */
    Ext.ux.util.MessageBus.subscribe(
        'de.intrabuild.groupware.email.EmailGrid.store.remove',
        _onEmailItemChange
    );
    Ext.ux.util.MessageBus.subscribe(
        'de.intrabuild.groupware.email.EmailGrid.store.update',
        _onEmailItemChange
    );

    return {

        /**
         * Renderer for the "subject" column of the email grid.
         *
         *
         * @param {Object} value The data value for the cell.
         * @param {Object} metadata An object in which you may set the following attributes:
         *                 - {String} css A CSS class name to add to the cell's TD element
         *                 - {String} attr An HTML attribute definition string to apply to
         *                                 the data container element within the table cell
         *                                 (e.g. 'style="color:red;"').
         * @param {Ext.data.Record} record The Ext.data.Record from which the data was extracted
         * @param {Number} rowIndex Row index
         * @param {Number} colIndex Column index
         * @param {Ext.data.Store} store
         *
         * @return {String}
         */
        renderSubjectColumn : function(value, metadata, record, rowIndex, colIndex, store)
        {
            if (_idCache.indexOf(record.id) != -1) {
                metadata.css = 'newItem';
            } else {
                var refTypes = record.get('referencedAsTypes').join(',');

                if (refTypes != '') {
                    var css = [];

                    if (refTypes.indexOf('reply') != -1) {
                        css.push('hasReply');
                    }

                    if (refTypes.indexOf('forward') != -1) {
                        css.push('hasForward');
                    }

                    metadata.css = css.join('');
                }
            }

            return value;
        },

        /**
         * Renderer for the "date" column of the email grid.
         *
         *
         * @param {Object} value The data value for the cell.
         * @param {Object} metadata An object in which you may set the following attributes:
         *                 - {String} css A CSS class name to add to the cell's TD element
         *                 - {String} attr An HTML attribute definition string to apply to
         *                                 the data container element within the table cell
         *                                 (e.g. 'style="color:red;"').
         * @param {Ext.data.Record} record The Ext.data.Record from which the data was extracted
         * @param {Number} rowIndex Row index
         * @param {Number} colIndex Column index
         * @param {Ext.data.Store} store
         *
         * @return {String}
         */
        renderDateColumn : function(value, metadata, record, rowIndex, colIndex, store)
        {
            if(!value){
                return "";
            }

            value = new Date(Date.parse(value));

            var dateParts = value.dateFormat(_dateFormat+' '+_timeFormat).split(' ');
            var today     = (new Date()).dateFormat(_dateFormat);

            if (dateParts[0] == today) {
                return dateParts[1];
            }

            return dateParts[0] + ' ' + dateParts[1];
        }

    };


}();