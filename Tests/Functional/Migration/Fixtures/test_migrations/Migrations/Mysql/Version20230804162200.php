<?php

declare(strict_types=1);

namespace KayStrobach\Migrations\TestFixtures\Migrations\Mysql;

use Doctrine\DBAL\Schema\Schema;
use KayStrobach\Migrations\Migration\AbstractDataHandlerMigration;

class Version20230804162200 extends AbstractDataHandlerMigration
{
    public function preUp(Schema $schema): void
    {
        parent::preUp($schema);

        $this->dataMap = [
            'pages' => [
                'NEW1234' => [
                    'pid' => 0,
                    'title' => 'My DataHandler test page',
                ],
            ],
        ];
    }
}
