<?php

declare(strict_types=1);

namespace <namespace>;

use AndreasWolf\Migrations\Migration\AbstractDataHandlerMigration;
use Doctrine\DBAL\Schema\Schema;
use Psr\Log\LoggerAwareTrait;

/**
* Auto-generated Migration: Please modify to your needs!
*/
final class Version<version> extends AbstractDataHandlerMigration {
	use LoggerAwareTrait;

	public function getDescription() : string
	{
		return '';
	}

	public function preUp(Schema $schema) : void
	{
		// this preUp() migration is auto-generated, please modify it to your needs
		<up>
	}

	public function down(Schema $schema) : void
	{
		// this down() migration is auto-generated, please modify it to your needs
		<down>
	}
}
