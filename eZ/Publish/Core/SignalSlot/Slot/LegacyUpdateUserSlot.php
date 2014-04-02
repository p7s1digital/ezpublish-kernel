<?php
/**
 * File containing the LegacyUpdateUserSlot class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\SignalSlot\Slot;

use eZ\Publish\Core\SignalSlot\Signal;
use eZContentCacheManager;
use eZContentObject;
use eZContentOperationCollection;

/**
 * A legacy slot handling UpdateUserSignal.
 */
class LegacyUpdateUserSlot extends AbstractLegacySlot
{
    /**
     * Receive the given $signal and react on it
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     *
     * @return void
     */
    public function receive( Signal $signal )
    {
        if ( !$signal instanceof Signal\UserService\UpdateUserSignal )
        {
            return;
        }

        $kernel = $this->getLegacyKernel();
        $kernel->runCallback(
            function () use ( $signal )
            {
                eZContentCacheManager::clearContentCacheIfNeeded( $signal->userId );
                eZContentOperationCollection::registerSearchObject( $signal->userId );
                eZContentObject::clearCache();// Clear all object memory cache to free memory
            },
            false
        );
    }
}
