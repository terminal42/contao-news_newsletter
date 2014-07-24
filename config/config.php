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
 * Add new notification type
 */
$GLOBALS['NOTIFICATION_CENTER']['NOTIFICATION_TYPE']['news_newsletter']['news_newsletter_default'] = array
(
     'recipients'           => array('admin_email', 'recipient_email'),
     'email_subject'        => array('admin_email', 'news_archive_*', 'news_*', 'news_text', 'news_url', 'recipient_email'),
     'email_text'           => array('admin_email', 'news_archive_*', 'news_*', 'news_text', 'news_url', 'recipient_email'),
     'email_html'           => array('admin_email', 'news_archive_*', 'news_*', 'news_text', 'news_url', 'recipient_email'),
     'file_name'            => array('admin_email', 'news_archive_*', 'news_*', 'news_text', 'news_url', 'recipient_email'),
     'file_content'         => array('admin_email', 'news_archive_*', 'news_*', 'news_text', 'news_url', 'recipient_email'),
     'email_recipient_cc'   => array('admin_email', 'news_archive_*', 'news_*', 'news_text', 'news_url', 'recipient_email'),
     'email_recipient_bcc'  => array('admin_email', 'news_archive_*', 'news_*', 'news_text', 'news_url', 'recipient_email'),
     'email_replyTo'        => array('admin_email', 'news_archive_*', 'news_*', 'news_text', 'news_url', 'recipient_email'),
     'attachment_tokens'    => array(),
);
