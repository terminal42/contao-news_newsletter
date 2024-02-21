<?php

declare(strict_types=1);

namespace Terminal42\NewsNewsletterBundle\ContaoManager;

use Codefog\HasteBundle\CodefogHasteBundle;
use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\NewsBundle\ContaoNewsBundle;
use Contao\NewsletterBundle\ContaoNewsletterBundle;
use Terminal42\NewsNewsletterBundle\Terminal42NewsNewsletterBundle;
use Terminal42\NotificationCenterBundle\Terminal42NotificationCenterBundle;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            (new BundleConfig(Terminal42NewsNewsletterBundle::class))
                ->setLoadAfter([
                    ContaoCoreBundle::class,
                    ContaoNewsBundle::class,
                    ContaoNewsletterBundle::class,
                    CodefogHasteBundle::class,
                    Terminal42NotificationCenterBundle::class,
                ])
                ->setReplace(['news_newsletter']),
        ];
    }
}
