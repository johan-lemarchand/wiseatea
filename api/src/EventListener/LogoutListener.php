<?php

namespace App\EventListener;

use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutListener
{
    /**
     * @param LogoutEvent $event
     * 
     * @return void
     */
    public function onLogout(LogoutEvent $event)
    {
        /**
         * @todo Code pour invalider le JWT
         */
    }
}