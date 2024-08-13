<?php

declare(strict_types=1);

namespace Terminal42\NewsNewsletterBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Terminal42\NewsNewsletterBundle\NotificationCenter\NewsNewsletterNotificationType;
use Terminal42\NotificationCenterBundle\NotificationCenter;

#[AsCallback(table: 'tl_news_archive', target: 'fields.nc_notification.options')]
class NotificationOptionsListener
{
    public function __construct(private readonly NotificationCenter $notificationCenter)
    {
    }

    /**
     * @return array<int, string>
     */
    public function __invoke(): array
    {
        return $this->notificationCenter->getNotificationsForNotificationType(NewsNewsletterNotificationType::NAME);
    }
}
