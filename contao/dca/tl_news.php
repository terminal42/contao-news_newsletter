<?php

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
    'icon'                => 'bundles/terminal42newsnewsletter/newsletter.png',
);
