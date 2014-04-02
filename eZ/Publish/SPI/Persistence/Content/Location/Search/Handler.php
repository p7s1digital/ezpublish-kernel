<?php
/**
 * File containing the Location Search Handler interface
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\SPI\Persistence\Content\Location\Search;

use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * The Location Search Handler interface defines search operations on Location elements in the storage engine.
 */
interface Handler
{
    /**
     * Finds locations for the given $query
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query $query
     */
    public function findLocations( Query $query );

    /**
     * Counts all locations given some $criterion.
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Query\Criterion $criterion
     *
     * @return int
     */
    public function getLocationCount( Criterion $criterion );
}
