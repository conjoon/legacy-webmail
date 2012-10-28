<?php
/**
 * conjoon
 * (c) 2002-2012 siteartwork.de/conjoon.org
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
 * @see Zend_Filter_Interface
 */
require_once 'Zend/Filter/Interface.php';

/**
 * @see Zend_Date
 */
require_once 'Zend/Date.php';

/**
 * @category   Filter
 * @package    Conjoon_Filter
 *
 * @author Thorsten Suckow-Homberg <tsuckow@conjoon.org>
 */
class Conjoon_Filter_DateIso8601 implements Zend_Filter_Interface
{
    /**
     * Defined by Zend_Filter_Interface
     *
     * Returns the ISO8601-date format for the specified value.
     * Will return the date for the 1970-1-1 if the passed argument
     * was not in a valid date-format.
     *
     * @param  mixed $value
     * @return integer
     */
    public function filter($value)
    {
        $d = strtotime($value);
        if ($d === false) {
            try {
                $date = new Zend_Date($value);
            } catch (Zend_Date_Exception $e) {
                $date = new Zend_Date("1970-01-01 00:00:00");
            }
        } else {
            try {
                $date = new Zend_Date($d);
            } catch (Zend_Date_Exception $e) {
                $date = new Zend_Date("1970-01-01 00:00:00");
            }
        }

        return $date->get(Zend_Date::ISO_8601);
    }
}
