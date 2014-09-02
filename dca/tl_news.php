<?php

/**
 * news_newsletter extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2014, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-news_newsletter
 */

/**
 * Add fields to tl_news
 */
$GLOBALS['TL_DCA']['tl_news']['fields']['newsletter'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_news']['newsletter'],
    'exclude'                 => true,
    'eval'                    => array('doNotCopy'=>true),
    'sql'                     => "char(1) NOT NULL default ''"
);

/**
 * Add the operation to tl_news
 */
$GLOBALS['TL_DCA']['tl_news']['list']['operations']['newsletter'] = array
(
    'label'               => &$GLOBALS['TL_LANG']['tl_news']['sendNewsletter'],
    'icon'                => 'system/modules/news_newsletter/assets/newsletter.png',
    'button_callback'     => array('tl_news_newsletter', 'newsletterIcon')
);

class tl_news_newsletter extends tl_news
{

    /**
     * Return the "newsletter" button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function newsletterIcon($row, $href, $label, $title, $icon, $attributes)
    {
        $objArchive = \NewsArchiveModel::findByPk($row['pid']);

        if (!$objArchive->newsletter || !$objArchive->newsletter_channel || !$objArchive->nc_notification) {
            return '';
        }

        // Toggle the record
        if (Input::get('newsletter'))
        {
             if ($this->sendNewsMessage(Input::get('newsletter'))) {
                 Message::addConfirmation($GLOBALS['TL_LANG']['tl_news']['message_news_newsletter_confirm']);
             } else {
                 Message::addError($GLOBALS['TL_LANG']['tl_news']['message_news_newsletter_error']);
             }

            $this->redirect($this->getReferer());
        }

        // Return just an image if newsletter was sent
        if ($row['newsletter']) {
            return Image::getHtml(str_replace('.png', '_.png', $icon), $label);
        }

        // Add the confirmation popup
        $intRecipients = \NewsletterRecipientsModel::countBy(array("pid=? AND active=1"), $objArchive->newsletter_channel);
        $attributes = 'onclick="if(!confirm(\'' . sprintf($GLOBALS['TL_LANG']['tl_news']['sendNewsletterConfirm'], $intRecipients) . '\'))return false;Backend.getScrollOffset()"';

        return '<a href="'.$this->addToUrl($href . '&newsletter=' . $row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
    }

    /**
     * Send the news message
     * @param integer
     * @return boolean
     */
    public function sendNewsMessage($intId)
    {
        $objNews = \NewsModel::findByPk($intId);

        if ($objNews === null) {
            return false;
        }

        $objArchive = $objNews->getRelated('pid');

        if ($objArchive === null || !$objArchive->newsletter || !$objArchive->newsletter_channel || !$objArchive->nc_notification) {
            return false;
        }

        $objNotification = \NotificationCenter\Model\Notification::findByPk($objArchive->nc_notification);

        if ($objNotification === null) {
            return false;
        }

        $objRecipients = \NewsletterRecipientsModel::findBy(array("pid=? AND active=1"), $objArchive->newsletter_channel);

        if ($objRecipients === null) {
            return false;
        }

        $arrTokens = array();

        // Generate news archive tokens
        foreach ($objArchive->row() as $k => $v) {
            $arrTokens['news_archive_' . $k] = \Haste\Util\Format::dcaValue('tl_news_archive', $k, $v);
        }

        // Generate news tokens
        foreach ($objNews->row() as $k => $v) {
            $arrTokens['news_' . $k] = \Haste\Util\Format::dcaValue('tl_news', $k, $v);
        }

        $arrTokens['news_text'] = '';
        $objElement = \ContentModel::findPublishedByPidAndTable($objNews->id, 'tl_news');

        // Generate news text
        if ($objElement !== null) {
            while ($objElement->next()) {
                $arrTokens['news_text'] .= $this->getContentElement($objElement->id);
            }
        }

        // Generate news URL
        $objPage = \PageModel::findWithDetails($objNews->getRelated('pid')->jumpTo);
        $arrTokens['news_url'] = ($objPage->rootUseSSL ? 'https://' : 'http://') . ($objPage->domain ?: \Environment::get('host')) . TL_PATH . '/' . $objPage->getFrontendUrl((($GLOBALS['TL_CONFIG']['useAutoItem'] && !$GLOBALS['TL_CONFIG']['disableAlias']) ?  '/' : '/items/') . ((!$GLOBALS['TL_CONFIG']['disableAlias'] && $objNews->alias != '') ? $objNews->alias : $objNews->id), $objPage->language);

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
