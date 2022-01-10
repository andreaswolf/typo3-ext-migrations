<?php

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] =
    \AndreasWolf\Migrations\Hooks\DataHandlerVersionAddHook::class;
