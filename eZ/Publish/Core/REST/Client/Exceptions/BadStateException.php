<?php
/**
 * File containing the BadStateException class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Exceptions;

use eZ\Publish\API\Repository\Exceptions\BadStateException as APIBadStateException;

/**
 * Implementation of the {@link \eZ\Publish\API\Repository\Exceptions\BadStateException}
 * interface.
 *
 * @see \eZ\Publish\API\Repository\Exceptions\BadStateException
 */
class BadStateException extends APIBadStateException
{
}
