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
 * Load the tl_module language
 */
\System::loadLanguageFile('tl_module');

/**
 * Extends tl_news_archive palette
 */
$GLOBALS['TL_DCA']['tl_news_archive']['palettes']['__selector__'][] = 'newsletter';
$GLOBALS['TL_DCA']['tl_news_archive']['palettes']['default'] = str_replace('jumpTo;', 'jumpTo;{newsletter_legend:hide},newsletter;', $GLOBALS['TL_DCA']['tl_news_archive']['palettes']['default']);
$GLOBALS['TL_DCA']['tl_news_archive']['subpalettes']['newsletter'] = 'newsletter_channel,nc_notification';

/**
 * Add the fields to tl_news_archive
 */
$GLOBALS['TL_DCA']['tl_news_archive']['fields']['newsletter'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_news_archive']['newsletter'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array('submitOnChange'=>true),
    'sql'                     => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_news_archive']['fields']['newsletter_channel'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_news_archive']['newsletter_channel'],
    'exclude'                 => true,
    'inputType'               => 'select',
    'foreignKey'              => 'tl_newsletter_channel.title',
    'eval'                    => array('mandatory'=>true, 'includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
    'sql'                     => "int(10) unsigned NOT NULL default '0'"
);

$GLOBALS['TL_DCA']['tl_news_archive']['fields']['nc_notification'] = array
(
    'label'                     => &$GLOBALS['TL_LANG']['tl_module']['nc_notification'],
    'exclude'                   => true,
    'inputType'                 => 'select',
    'options_callback'          => array('tl_news_archive_newsletter', 'getNotificationChoices'),
    'eval'                      => array('mandatory'=>true, 'includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
    'sql'                       => "int(10) unsigned NOT NULL default '0'",
    'relation'                  => array('type'=>'hasOne', 'load'=>'lazy', 'table'=>'tl_nc_notification'),
);

class tl_news_archive_newsletter
{

    /**
     * Get notification choices
     * @param   \DataContainer
     * @return  array
     */
    public function getNotificationChoices(\DataContainer $dc)
    {
        $arrChoices = array();
        $objNotifications = \Database::getInstance()->execute("SELECT id,title FROM tl_nc_notification WHERE type='news_newsletter_default' ORDER BY title");

        while ($objNotifications->next()) {
            $arrChoices[$objNotifications->id] = $objNotifications->title;
        }

        return $arrChoices;
    }
}
