<?php

declare(strict_types=1);

namespace Terminal42\NewsNewsletterBundle\EventListener;

use Codefog\HasteBundle\Formatter;
use Contao\Backend;
use Contao\ContentModel;
use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\Image;
use Contao\Input;
use Contao\Message;
use Contao\NewsArchiveModel;
use Contao\NewsletterRecipientsModel;
use Contao\NewsModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Terminal42\NotificationCenterBundle\Exception\Parcel\CouldNotCreateParcelException;
use Terminal42\NotificationCenterBundle\NotificationCenter;

#[AsCallback(table: 'tl_news', target: 'list.operations.newsletter.button')]
class SendNewsletterListener
{
    public function __construct(
        private readonly NotificationCenter $notificationCenter,
        private readonly Formatter $formatter,
    ) {
    }

    /**
     * @param array<string, string|int> $row
     * @param string|null               $href
     * @param string|null               $label
     * @param string|null               $title
     * @param string|null               $icon
     */
    public function __invoke(array $row, $href, $label, $title, $icon): string
    {
        $archive = NewsArchiveModel::findById($row['pid']);

        if (null === $archive || !$archive->newsletter || !$archive->newsletter_channel || !$archive->nc_notification) {
            return '';
        }

        // Toggle the record
        if (Input::get('newsletter')) {
            if ($this->sendNewsMessage((int) Input::get('newsletter'))) {
                Message::addConfirmation($GLOBALS['TL_LANG']['tl_news']['message_news_newsletter_confirm']);
            } else {
                Message::addError($GLOBALS['TL_LANG']['tl_news']['message_news_newsletter_error']);
            }

            throw new RedirectResponseException(System::getReferer());
        }

        // Return just an image if newsletter was sent
        if ($row['newsletter']) {
            return Image::getHtml(str_replace('.png', '_.png', (string) $icon), $label);
        }

        // Add the confirmation popup
        $intRecipients = NewsletterRecipientsModel::countBy(["pid=? AND active='1'"], $archive->newsletter_channel);
        $attributes = 'onclick="if(!confirm(\''.\sprintf($GLOBALS['TL_LANG']['tl_news']['sendNewsletterConfirm'], $intRecipients).'\'))return false;Backend.getScrollOffset()"';

        return '<a href="'.Backend::addToUrl($href.'&newsletter='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
    }

    private function sendNewsMessage(int $newsletterId): bool
    {
        $news = NewsModel::findById($newsletterId);

        if (null === $news) {
            return false;
        }

        $newsArchive = $news->getRelated('pid');

        if (null === $newsArchive || !$newsArchive->newsletter || !$newsArchive->newsletter_channel || !$newsArchive->nc_notification) {
            return false;
        }

        $recipients = NewsletterRecipientsModel::findBy(['pid=? AND active=1'], $newsArchive->newsletter_channel);

        if (null === $recipients) {
            return false;
        }

        $tokens = [];

        // Generate news archive tokens
        foreach ($newsArchive->row() as $k => $v) {
            $tokens['news_archive_'.$k] = $this->formatter->dcaValue('tl_news_archive', $k, $v);
        }

        // Generate news tokens
        foreach ($news->row() as $k => $v) {
            $tokens['news_'.$k] = $this->formatter->dcaValue('tl_news', $k, $v);
        }

        $tokens['news_text'] = '';
        $elements = ContentModel::findPublishedByPidAndTable($news->id, 'tl_news');

        // Generate news text
        if (null !== $elements) {
            foreach ($elements as $element) {
                $tokens['news_text'] .= Controller::getContentElement($element->id);
            }
        }

        // Generate news URL
        if (null !== ($objPage = PageModel::findWithDetails($news->getRelated('pid')->jumpTo))) {
            $tokens['news_url'] = $objPage->getAbsoluteUrl('/'.($news->alias ?: $news->id));
        }

        foreach ($recipients as $recipient) {
            $tokens['recipient_email'] = $recipient->email;

            try {
                $this->notificationCenter->sendNotification($newsArchive->nc_notification, $tokens);
            } catch (CouldNotCreateParcelException) {
                return false;
            }
        }

        // Set the newsletter flag
        $news->newsletter = 1;
        $news->save();

        return true;
    }
}
