# Migrate with doctrine migrations

__It's strongly recommend to use the "typo3" CLI binary!__

To get the status of your migrations you can run:

    <path-to-bin>/typo3 migrations:status

To execute all pending migrations you can run:

    <path-to-bin>/typo3 migrations:migrate

This will give you an output like this:

```
 == Configuration
    >> Name:                                               Doctrine Database Migrations
    >> Database Driver:                                    pdo_mysql
    >> Database Name:                                      myproject
    >> Configuration Source:                               manually configured
    >> Version Table Name:                                 doctrine_migrationstatus
    >> Migrations Namespace:                               KayStrobach\Migrations\Persistence\Doctrine\Migrations
    >> Migrations Target Directory:                        /var/www/my-project//fileadmin/Migrations
    >> Current Version:                                    0
    >> Latest Version:                                     2014-07-14 18:44:53 (20140714184453)
    >> Executed Migrations:                                0
    >> Available Migrations:                               1
    >> New Migrations:                                     1

 == Migration Versions
    >> 2014-07-14 18:44:53 (20140714184453) migrations                  not migrated
```

This extension uses `doctrine/migrations` to migrate the database tables.

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
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql');
        $this->addSql(
            "CREATE TABLE test (textfield VARCHAR(40) NOT NULL) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB"
        );
    }

    public function down(Schema $schema): void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql');

        $this->addSql("Drop Table test");
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
