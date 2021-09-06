<?php

declare(strict_types=1);

namespace Terminal42\NewsNewsletterBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Doctrine\DBAL\Connection;

/**
 * @Callback(table="tl_news_archive", target="fields.nc_notification.options")
 */
class NotificationOptionsListener
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function __invoke(): array
    {
        $options = [];
        $notifications = $this->connection->fetchAllAssociative("SELECT id, title FROM tl_nc_notification WHERE type='news_newsletter_default' ORDER BY title");

        foreach ($notifications as $notification) {
            $options[$notification['id']] = $notification['title'];
        }

        return $options;
    }
}
