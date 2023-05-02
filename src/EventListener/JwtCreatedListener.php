<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class JwtCreatedListener
{
    public function onJwtCreated(JWTCreatedEvent $event)
    {
        $user = $event->getUser();

        $payload = $event->getData();
        $payload['firstname'] = $user->getFirstname();
        $payload['lastname'] = $user->getLastname();

        $event->setData($payload);
    }
}
