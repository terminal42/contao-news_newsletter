<?php

/**
 * ??? extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2008-2014, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-???
 */

/**
 * Replace the "toggle" button
 */
$GLOBALS['TL_DCA']['tl_news']['list']['operations']['toggle']['button_callback'] = array('tl_news_newsletter', 'toggleIcon');

class tl_news_newsletter extends tl_news
{

    /**
     * Return the "toggle visibility" button
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        $objArchive = \NewsArchiveModel::findByPk($row['pid']);

        if (!$objArchive->newsletter || !$objArchive->newsletter_channel || !$objArchive->nc_notification) {
            return parent::toggleIcon($row, $href, $label, $title, $icon, $attributes);
        }

        // Toggle the record
        if (strlen(Input::get('tid')))
        {
            $this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1));

            // Send the message
            if ((Input::get('state') == 1)) {
                 if ($this->sendNewsMessage(Input::get('tid'))) {
                     Message::addConfirmation($GLOBALS['TL_LANG']['tl_news']['message_news_newsletter_confirm']);
                 } else {
                     Message::addError($GLOBALS['TL_LANG']['tl_news']['message_news_newsletter_error']);
                 }
            }

            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_news::published', 'alexf'))
        {
            return '';
        }

        $href .= '&amp;tid='.$row['id'].'&amp;state='.($row['published'] ? '' : 1);

        if (!$row['published'])
        {
            $icon = 'invisible.gif';
        }

        // Remove the AJAX toggle
        $attributes = 'onclick="Backend.getScrollOffset();"';

        return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
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

        $objRecipients = \NewsletterRecipientsModel::findByPid($objArchive->newsletter_channel);

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

        // Administrator e-mail
        $arrTokens['admin_email'] = $GLOBALS['TL_ADMIN_EMAIL'];

        while ($objRecipients->next()) {
            $arrTokens['recipient_email'] = $objRecipients->email;
            $objNotification->send($arrTokens);
        }

        return true;
    }
}
