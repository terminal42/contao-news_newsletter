<?php

declare(strict_types=1);

namespace Terminal42\NewsNewsletterBundle\EventListener;

use Contao\Backend;
use Contao\Controller;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\Image;
use Contao\Input;
use Contao\Message;
use Contao\NewsArchiveModel;
use Contao\NewsModel;
use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Haste\Util\Format;
use NotificationCenter\Model\Notification;

/**
 * @Callback(table="tl_news", target="list.operations.newsletter.button")
 */
class SendNewsletterListener
{
    public function __invoke(array $row, $href, $label, $title, $icon): string
    {
        $archive = NewsArchiveModel::findByPk($row['pid']);

        if (null === $archive || !$archive->newsletter || !$archive->newsletter_channel || !$archive->nc_notification) {
            return '';
        }

        // Toggle the record
        if (Input::get('newsletter')) {
            if ($this->sendNewsMessage(Input::get('newsletter'))) {
                Message::addConfirmation($GLOBALS['TL_LANG']['tl_news']['message_news_newsletter_confirm']);
            } else {
                Message::addError($GLOBALS['TL_LANG']['tl_news']['message_news_newsletter_error']);
            }

            throw new RedirectResponseException(System::getReferer());
        }

        // Return just an image if newsletter was sent
        if ($row['newsletter']) {
            return Image::getHtml(str_replace('.png', '_.png', $icon), $label);
        }

        // Add the confirmation popup
        $intRecipients = \NewsletterRecipientsModel::countBy(["pid=? AND active='1'"], $archive->newsletter_channel);
        $attributes = 'onclick="if(!confirm(\''.sprintf($GLOBALS['TL_LANG']['tl_news']['sendNewsletterConfirm'], $intRecipients).'\'))return false;Backend.getScrollOffset()"';

        return '<a href="'.Backend::addToUrl($href.'&newsletter='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
    }

    private function sendNewsMessage($intId)
    {
        $objNews = NewsModel::findByPk($intId);

        if (null === $objNews) {
            return false;
        }

        $objArchive = $objNews->getRelated('pid');

        if (null === $objArchive || !$objArchive->newsletter || !$objArchive->newsletter_channel || !$objArchive->nc_notification) {
            return false;
        }

        $objNotification = Notification::findByPk($objArchive->nc_notification);

        if (null === $objNotification) {
            return false;
        }

        $objRecipients = \NewsletterRecipientsModel::findBy(['pid=? AND active=1'], $objArchive->newsletter_channel);

        if (null === $objRecipients) {
            return false;
        }

        $arrTokens = [];

        // Generate news archive tokens
        foreach ($objArchive->row() as $k => $v) {
            $arrTokens['news_archive_'.$k] = Format::dcaValue('tl_news_archive', $k, $v);
        }

        // Generate news tokens
        foreach ($objNews->row() as $k => $v) {
            $arrTokens['news_'.$k] = Format::dcaValue('tl_news', $k, $v);
        }

        $arrTokens['news_text'] = '';
        $objElement = \ContentModel::findPublishedByPidAndTable($objNews->id, 'tl_news');

        // Generate news text
        if (null !== $objElement) {
            while ($objElement->next()) {
                $arrTokens['news_text'] .= Controller::getContentElement($objElement->id);
            }
        }

        // Generate news URL
        if (null !== ($objPage = PageModel::findWithDetails($objNews->getRelated('pid')->jumpTo))) {
            $arrTokens['news_url'] = $objPage->getAbsoluteUrl((($GLOBALS['TL_CONFIG']['useAutoItem']) ? '/' : '/items/').($objNews->alias ?: $objNews->id));
        }

        // Administrator e-mail
        $arrTokens['admin_email'] = $GLOBALS['TL_ADMIN_EMAIL'];

        while ($objRecipients->next()) {
            $arrTokens['recipient_email'] = $objRecipients->email;
            $objNotification->send($arrTokens);
        }

        // Set the newsletter flag
        $objNews->newsletter = 1;
        $objNews->save();

        return true;
    }
}
