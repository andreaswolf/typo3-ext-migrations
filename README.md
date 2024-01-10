# Migrate with doctrine migrations

This extension uses `doctrine/migrations` to migrate the database tables.

__You should use the "typo3" CLI binary or "typo3cms" console and not `bin/doctrine-migrations`!__

To get the status of your migrations you can run:

    <path-to-bin>/typo3 migrations:status

To execute all pending migrations you can run:

    <path-to-bin>/typo3 migrations:migrate

# Upgrade from TYPO3 9/10 to 11/12

Moving to TYPO3 11, this extensions switched from doctrine/migration 2.x to 3.x for compatibility
with PHP 8.

After running the Database Compare migrations, don't forget to run the Upgrade Wizards which will
migrate the table `migration_statustable`:
```
bin/typo3 upgrade:run migration_statustable
```

Replace the `@namespace` setting with the namespace of your extension.

# Own migration

Create a folder called `Migrations/Mysql/` in your extension and place a file (`Version20220910184453.php`) with this content:

```php
<?php

namespace Your\Extension\Migrations\Mysql;

use Doctrine\DBAL\Exception;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20220910184453 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql(
            "UPDATE tt_content SET bodytext = 'Hello World' WHERE uid = 1"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE tt_content SET bodytext = 'Hello previous version' WHERE uid = 1");
    }
}
```

In your extension `composer.json`, configure:
```
...
  "autoload": {
    "psr-4": {
      "Your\\Extension\\": "Classes/",
      "Your\\Extension\\Migrations\\": "Migrations/"
    }
  },
...
```

Then you should see one pending migration with the `migrations:status` command.

## Using DataHandler

If you want to perform migrations using the TYPO3 `DataHandler` you can extend
the `AbstractDataHandlerMigration` instead. The most basic version only fills
the `$dataMap` or `$commandMap` property:

```php
<?php
namespace Your\Extension\Migrations\Mysql;

use KayStrobach\Migrations\Migration\AbstractDataHandlerMigration;

class Version20190528172200 extends AbstractDataHandlerMigration
{
    /**
     * @var array
     */
    protected $dataMap = [
        'pages' => [
            'NEW123' => [
                'title' => 'Test',
            ],
        ],
    ];
}
```

For more advanced cases you can override the `preUp()` method to fill
the `$dataMap` or `$commandMap`:

```php
<?php
namespace Your\Extension\Migrations\Mysql;

use Doctrine\DBAL\Schema\Schema;
use KayStrobach\Migrations\Migration\AbstractDataHandlerMigration;

class Version20190528172400 extends AbstractDataHandlerMigration
{
    public function preUp(Schema $schema): void
    {
        parent::preUp($schema);

        // Perform logic to fill $dataMap
        $this->dataMap = ...;
    }
}
```

# More information

* https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/
* https://www.doctrine-project.org/projects/doctrine-migrations/en/2.3/index.html
