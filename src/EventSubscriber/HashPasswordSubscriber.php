<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\AdminUser;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class HashPasswordSubscriber implements EventSubscriberInterface
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function prePersist(LifecycleEventArgs $eventArgs): void
    {
        $this->hashPassword($eventArgs);
    }

    public function preUpdate(LifecycleEventArgs $eventArgs): void
    {
        $this->hashPassword($eventArgs);
    }

    public function hashPassword(LifecycleEventArgs $eventArgs): void
    {
        $entity = $eventArgs->getEntity();
        if(!$entity instanceof AdminUser)
        {
            return;
        }

        if(null !== $entity->getPlainPassword())
        {
            $entity->setPassword($this->passwordHasher->hashPassword($entity,$entity->getPlainPassword()));
        }
    }
}