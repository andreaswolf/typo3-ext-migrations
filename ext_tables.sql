CREATE TABLE pages
(
	tx_migrations_version VARCHAR(14) DEFAULT '' NOT NULL
);

CREATE TABLE tt_content
(
	tx_migrations_version VARCHAR(14) DEFAULT '' NOT NULL
);

CREATE TABLE doctrine_migrationstatus
(
	version VARCHAR(191) NOT NULL PRIMARY KEY,
	executed_at DATETIME DEFAULT NULL,
	execution_time INT(11) DEFAULT NULL,
);
