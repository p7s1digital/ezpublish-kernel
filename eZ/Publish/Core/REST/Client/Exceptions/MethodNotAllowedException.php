<?php
/**
 * File containing the MethodNotAllowedException class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Exceptions;

use BadMethodCallException;

/**
 * Exception thrown if an unsupported method is called.
 */
class MethodNotAllowedException extends BadMethodCallException
{
}
