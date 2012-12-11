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


namespace Conjoon\Mail\Client\Service\ServicePatron;

use Conjoon\Lang\MissingKeyException;

/**
 * @see \Conjoon\Lang\MissingKeyException
 */
require_once 'Conjoon/Lang/MissingKeyException.php';

/**
 * @see \Conjoon\Mail\Client\Service\ServicePatron\AbstractServicePatron
 */
require_once 'Conjoon/Mail/Client/Service/ServicePatron/AbstractServicePatron.php';

/**
 * @see \Conjoon\Mail\Client\Service\ServicePatron\ServicePatronException
 */
require_once 'Conjoon/Mail/Client/Service/ServicePatron/ServicePatronException.php';

/**
 * A service patron for downloading an email attachment.
 *
 * @package Conjoon
 * @category Conjoon\Mail
 *
 * @author Thorsten Suckow-Homberg <tsuckow@conjoon.org>
 */
class DownloadAttachmentPatron
    extends \Conjoon\Mail\Client\Service\ServicePatron\AbstractServicePatron {

    /**
     * @inheritdoc
     */
    public function applyForData(array $data)
    {
        try {

            $d =& $data;

            $d['name']    = $this->v('fileName', $d);
            $d['resource'] = $this->v('encoding', $d) == 'quoted-printable'
                            ? quoted_printable_decode($this->v('content', $d))
                            : $d['encoding'] == 'base64'
                              ? base64_decode($this->v('content', $d))
                              : $this->v('content', $d);
            $d['mimeType'] = $this->v('mimeType', $d)
                             ? $data['mimeType'] : 'text/plain';

            unset($d['fileName']);
            unset($d['content']);

        } catch (\Exception $e) {
            throw new ServicePatronException(
                "Exception thrown by previous exception: " . $e->getMessage(),
                0, $e
            );
        }

        return $data;
    }

}