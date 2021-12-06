<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Advert;
use App\Repository\AdminUserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class sendNotificationEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private MailerInterface $mailer, private AdminUserRepository $adminUserRepository)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['sendEmail', EventPriorities::POST_WRITE]
        ];
    }

    public function sendEmail(ViewEvent $event): void
    {
        $entity = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if(Request::METHOD_POST !== $method || !$entity instanceof Advert)
        {
            return;
        }

        $adminUsers = $this->adminUserRepository->findAll();
        foreach ($adminUsers as $admin)
        {
            $email = (new TemplatedEmail())
                ->to(new Address($admin->getEmail(), $admin->getUsername()))
                ->subject('New advert !')
                ->htmlTemplate('email/advert_confirm_admin.html.twig')
                ->context(['advert' => $entity, 'admin' => $admin]);

            $this->mailer->send($email);
        }
    }
}