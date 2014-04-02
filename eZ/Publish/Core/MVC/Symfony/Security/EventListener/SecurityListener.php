<?php
/**
 * File containing the SecurityListener class.
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\Security\EventListener;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use eZ\Publish\Core\MVC\Symfony\Security\InteractiveLoginToken;
use eZ\Publish\Core\MVC\Symfony\Security\UserInterface as eZUser;
use eZ\Publish\API\Repository\Values\User\User as APIUser;
use eZ\Publish\Core\MVC\Symfony\Event\InteractiveLoginEvent;
use eZ\Publish\Core\MVC\Symfony\Security\UserWrapped;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent as BaseInteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Security\Core\User\UserInterface;

class SecurityListener implements EventSubscriberInterface
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;

    /**
     * @var \eZ\Publish\Core\MVC\ConfigResolverInterface
     */
    private $configResolver;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    private $securityContext;

    public function __construct( Repository $repository, ConfigResolverInterface $configResolver, EventDispatcherInterface $eventDispatcher, SecurityContextInterface $securityContext )
    {
        $this->repository = $repository;
        $this->configResolver = $configResolver;
        $this->eventDispatcher = $eventDispatcher;
        $this->securityContext = $securityContext;
    }

    public static function getSubscribedEvents()
    {
        return array(
            SecurityEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin'
        );
    }

    /**
     * Tries to retrieve a valid eZ user if authenticated user doesn't come from the repository (foreign user provider).
     * Will dispatch an event allowing listeners to return a valid eZ user for current authenticated user.
     * Will by default let the repository load the anonymous user.
     *
     * @param \Symfony\Component\Security\Http\Event\InteractiveLoginEvent $event
     */
    public function onInteractiveLogin( BaseInteractiveLoginEvent $event )
    {
        $token = $event->getAuthenticationToken();
        $originalUser = $token->getUser();
        if ( $originalUser instanceof eZUser || !$originalUser instanceof UserInterface )
        {
            return;
        }

        /*
         * 1. Send the event.
         * 2. If no eZ user is returned, load Anonymous user.
         * 3. Inject eZ user in repository.
         * 4. Create the UserWrapped user object (implementing eZ UserInterface) with loaded eZ user.
         * 5. Create new token with UserWrapped user
         * 6. Inject the new token in security context
         */
        $subLoginEvent = new InteractiveLoginEvent( $event->getRequest(), $token );
        $this->eventDispatcher->dispatch( MVCEvents::INTERACTIVE_LOGIN, $subLoginEvent );

        if ( $subLoginEvent->hasAPIUser() )
        {
            $apiUser = $subLoginEvent->getAPIUser();
        }
        else
        {
            $apiUser = $this->repository->getUserService()->loadUser(
                $this->configResolver->getParameter( "anonymous_user_id" )
            );
        }

        $this->repository->setCurrentUser( $apiUser );

        $providerKey = method_exists( $token, 'getProviderKey' ) ? $token->getProviderKey() : __CLASS__;
        $interactiveToken = new InteractiveLoginToken(
            $this->getUser( $originalUser, $apiUser ),
            get_class( $token ),
            $token->getCredentials(),
            $providerKey,
            $token->getRoles()
        );
        $interactiveToken->setAttributes( $token->getAttributes() );
        $this->securityContext->setToken( $interactiveToken );
    }

    /**
     * Returns new user object based on original user and provided API user.
     * One may want to override this method to use their own user class.
     *
     * @param \Symfony\Component\Security\Core\User\UserInterface $originalUser
     * @param \eZ\Publish\API\Repository\Values\User\User $apiUser
     *
     * @return \eZ\Publish\Core\MVC\Symfony\Security\UserInterface
     */
    protected function getUser( UserInterface $originalUser, APIUser $apiUser )
    {
        return new UserWrapped( $originalUser, $apiUser );
    }
}
