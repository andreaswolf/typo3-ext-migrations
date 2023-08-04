<?php

declare(strict_types=1);

namespace KayStrobach\Migrations\TestFixtures\Migrations\Mysql;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

class Version20230804102700 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('INSERT INTO pages (title, uid, pid) VALUES ("My test page", 1, 0)');
    }

    public function down(Schema $schema): void
    {
    }
}
