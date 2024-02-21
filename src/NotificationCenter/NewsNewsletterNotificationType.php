<?php

declare(strict_types=1);

namespace Terminal42\NewsNewsletterBundle\NotificationCenter;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Terminal42\NotificationCenterBundle\NotificationType\NotificationTypeInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\AnythingTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\EmailTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\HtmlTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\TextTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\TokenDefinitionInterface;

#[Autoconfigure(tags: ['notification_center.type'], public: true)]
class NewsNewsletterNotificationType implements NotificationTypeInterface
{
    final public const NAME = 'news_newsletter';

    public function __construct(private readonly TokenDefinitionFactoryInterface $factory)
    {
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getTokenDefinitions(): array
    {
        return [
            $this->factory->create(EmailTokenDefinition::class, 'recipient_email', 'newsletter.recipient_email'),
            $this->createDefinition(HtmlTokenDefinition::class, 'news_text'),
            $this->createDefinition(TextTokenDefinition::class, 'news_url'),
            $this->createDefinition(AnythingTokenDefinition::class, 'news_archive_*'),
            $this->createDefinition(AnythingTokenDefinition::class, 'news_*'),
        ];
    }

    private function createDefinition(string $definitionClass, string $tokenName): TokenDefinitionInterface
    {
        return $this->factory->create($definitionClass, $tokenName, self::NAME.'.'.$tokenName);
    }
}
