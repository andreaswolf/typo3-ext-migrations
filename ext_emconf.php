<?php

$EM_CONF[$_EXTKEY] = [
	'title' => 'Migrations',
	'description' => 'Doctrine migrations integration for TYPO3 CMS',
	'category' => 'service',
	'version' => '1.0.0',
	'state' => 'stable',
	'clearCacheOnLoad' => 0,
	'author' => 'Andreas Wolf',
	'author_email' => 'dev@a-w.io',
	'constraints' => [
		'depends' => [
            'typo3' => '10.0.0-11.99.99',
        ],
		'conflicts' => [
        ],
		'suggests' => [
        ],
    ],
];
