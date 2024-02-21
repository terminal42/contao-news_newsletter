<?php

$GLOBALS['TL_DCA']['tl_news']['list']['operations']['newsletter'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_news']['sendNewsletter'],
    'icon' => 'bundles/terminal42newsnewsletter/newsletter.png',
];

$GLOBALS['TL_DCA']['tl_news']['fields']['newsletter'] = [
    'exclude' => true,
    'eval' => ['doNotCopy' => true],
    'sql' => "char(1) NOT NULL default ''",
];
