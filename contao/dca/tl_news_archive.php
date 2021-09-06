<?php

use Contao\CoreBundle\DataContainer\PaletteManipulator;

\System::loadLanguageFile('tl_module');

PaletteManipulator::create()
    ->addLegend('newsletter_legend', 'title_legend', PaletteManipulator::POSITION_AFTER, true)
    ->addField('newsletter', 'newsletter_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_news_archive')
;

$GLOBALS['TL_DCA']['tl_news_archive']['palettes']['__selector__'][] = 'newsletter';
$GLOBALS['TL_DCA']['tl_news_archive']['subpalettes']['newsletter'] = 'newsletter_channel,nc_notification';

/**
 * Add the fields to tl_news_archive
 */
$GLOBALS['TL_DCA']['tl_news_archive']['fields']['newsletter'] = array
(
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array('submitOnChange'=>true),
    'sql'                     => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_news_archive']['fields']['newsletter_channel'] = array
(
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
    'eval'                      => array('mandatory'=>true, 'includeBlankOption'=>true, 'chosen'=>true, 'tl_class'=>'w50'),
    'sql'                       => "int(10) unsigned NOT NULL default '0'",
    'relation'                  => array('type'=>'hasOne', 'load'=>'lazy', 'table'=>'tl_nc_notification'),
);
