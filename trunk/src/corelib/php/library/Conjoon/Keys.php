<?php
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

 /**
 * A collection of constants defining keys for registry- and session-entries.
 *
 * @author Thorsten Suckow-Homberg <ts@siteartwork.de>
 */
interface Conjoon_Keys {

// -------- registry
    const REGISTRY_AUTH_OBJECT = 'com.conjoon.registry.authObject';

// -------- ext request object
    const EXT_REQUEST_OBJECT = 'com.conjoon.registry.extRequestObject';

// -------- app config in registry
    const REGISTRY_CONFIG_OBJECT = 'com.conjoon.registry.config';

// -------- session auth namespace
    const SESSION_AUTH_NAMESPACE = 'com.conjoon.session.authNamespace';

// -------- session reception controller
    const SESSION_CONTROLLER_RECEPTION = 'com.conjoon.session.receptionController';

// -------- cache key emails
    const CACHE_EMAIL_MESSAGE = 'com.conjoon.cache.email.message';

// -------- cache key feed items
    const CACHE_FEED_ITEM = 'com.conjoon.cache.feed.item';

// -------- cache key feed items
    const CACHE_FEED_ACCOUNTS = 'com.conjoon.cache.feed.accounts';

// -------- cache db metadata
    const CACHE_DB_METADATA = 'com.conjoon.cache.db.metadata';

// -------- cache twitter accounts
    const CACHE_TWITTER_ACCOUNTS = 'com.conjoon.cache.twitter.accounts';
}