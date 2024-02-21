<?php

declare(strict_types=1);

namespace Terminal42\NewsNewsletterBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

class NotificationTypeMigration extends AbstractMigration
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist('tl_nc_notification')) {
            return false;
        }

        $columns = $schemaManager->listTableColumns('tl_nc_notification');

        if (!\array_key_exists('type', $columns)) {
            return false;
        }

        return $this->connection->fetchOne("SELECT COUNT(*) FROM tl_nc_notification WHERE type='news_newsletter_default'") > 0;
    }

    public function run(): MigrationResult
    {
        $this->connection->executeStatement("UPDATE tl_nc_notification SET type='news_newsletter' WHERE type='news_newsletter_default'");

        return $this->createResult(true);
    }
}
