<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\Advert;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Workflow\Event\Event;

class PublishAdvertSubscriber implements EventSubscriberInterface
{
    public function __construct(private MailerInterface $mailer)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.advert.enter.published' => 'setPublishedAt',
        ];
    }

    public function setPublishedAt(Event $event): void
    {
        /** @var Advert $advert */
        $advert = $event->getSubject();
        $advert->setPublishedAt(new \DateTime());
        $this->sendEmail($advert);
    }

    private function sendEmail(Advert $advert)
    {
        $email = (new TemplatedEmail())
            ->to(new Address($advert->getEmail()))
            ->subject('Advert Published !')
            ->htmlTemplate('email/advert_published.html.twig')
            ->context(['advert' => $advert]);

        $this->mailer->send($email);
    }
}