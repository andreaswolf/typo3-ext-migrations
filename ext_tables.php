<?php

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
    'KayStrobach.' . $_EXTKEY,
    'system',          // Main area
    'mod1',         // Name of the module
    '',             // Position of the module
    array(          // Allowed controller action combinations
        'Doctrine' => 'index,show,new,create,delete,deleteAll,edit,update,populate',
    ),
    array(          // Additional configuration
        'access'    => 'user,group',
        'icon'      => 'EXT:migrations/ext_icon.gif',
        'labels'    => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_mod.xml',
    )
);