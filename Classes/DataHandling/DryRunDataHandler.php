<?php

declare(strict_types=1);

namespace KayStrobach\Migrations\DataHandling;

use TYPO3\CMS\Core\DataHandling\DataHandler;

class DryRunDataHandler extends DataHandler
{
    public function process_cmdmap()
    {
        // no-op
    }

    public function process_datamap()
    {
        // no-op
    }
}
