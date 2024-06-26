<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Tests\Integration\Legacy\Multilanguage;

use oxDb;
use OxidEsales\Eshop\Core\TableViewNameGenerator;
use OxidEsales\EshopCommunity\Core\Registry;

final class AdditionalTablesTest extends MultilanguageTestCase
{
    /**
     * Additional multilanguage tables.
     */
    private array $additionalTables = [];

    /**
     * Fixture tearDown.
     */
    public function tearDown(): void
    {
        $this->setConfigParam('aMultiLangTables', []);
        $this->updateViews();

        foreach ($this->additionalTables as $name) {
            $this->removeAdditionalTables($name);
        }
        $this->removeAdditionalTables('set1');

        parent::tearDown();
    }

    /**
     * Assert that set tables are automatically created for additional multilanguage table
     * in case we add first the table and then create the languages.
     */
    public function testCreateLanguagesAfterAdditionalTable(): void
    {
        $this->createTable('addtest');
        Registry::getConfig()->setConfigParam('aMultiLangTables', ['addtest']);

        //add nine more languages
        $this->prepare(9);

        $tableSchemaQuery = "SELECT TABLE_NAME, TABLE_COLLATION  FROM INFORMATION_SCHEMA.TABLES  WHERE TABLE_NAME LIKE 'addtest_set1'";
        $result = oxDb::getDb(oxDb::FETCH_MODE_ASSOC)->getRow($tableSchemaQuery);

        self::assertEquals([
            'TABLE_NAME' => 'addtest_set1',
            'TABLE_COLLATION' => 'latin1_general_ci',
        ], $result);

        $charset_query = "SELECT character_set_name FROM information_schema.`COLUMNS` 
                            WHERE table_name = 'addtest_set1'
                              AND column_name = 'TITLE_8';";

        $result = oxDb::getDb(oxDb::FETCH_MODE_ASSOC)->getOne($charset_query);

        self::assertEquals('latin1', $result);
    }

    /**
     * Assert that set tables are automatically created for additional multilanguage table
     * in case first create the languages, then set the table in config.inc.php variable 'aMultiLangTables'
     * and call updateViews. Without *_set1 tables, view creating throws and exception.
     */
    public function testCreateAdditionalTableAfterCreatingLanguages(): void
    {
        //add nine more languages
        $this->prepare(9);

        $this->createTable('addtest');
        $this->setConfigParam('aMultiLangTables', ['addtest']);

        $this->updateViews();

        $sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES  WHERE TABLE_NAME LIKE 'addtest_set1'";
        $result = oxDb::getDb(oxDb::FETCH_MODE_ASSOC)->getOne($sql);
        $this->assertEquals('addtest_set1', $result);
    }

    /**
     * Verify that the expected data turned up in the language views
     */
    public function testViewContentsCreateLanguagesAfterAdditionalTable(): void
    {
        $oxid = '_test101';

        $this->createTable('addtest');
        $this->setConfigParam('aMultiLangTables', ['addtest']);

        //add nine more languages
        $languageId = $this->prepare(9);

        //insert testdata for language id 0
        $sql = "INSERT INTO addtest (OXID, TITLE) VALUES ('" . $oxid . "', 'some default title')";
        oxDb::getDb()->execute($sql);

        //insert testdata for last added language id in set1 table
        $sql = 'INSERT INTO addtest_set1 (OXID, TITLE_' . $languageId . ") VALUES ('" . $oxid . "', 'some additional title')";
        oxDb::getDb()->execute($sql);

        $tableViewNameGenerator = oxNew(TableViewNameGenerator::class);
        $sql = 'SELECT TITLE FROM ' . $tableViewNameGenerator->getViewName(
            'addtest',
            $languageId
        ) . " WHERE OXID = '" . $oxid . "'";
        $this->assertSame('some additional title', oxDb::getDb()->getOne($sql));

        $sql = 'SELECT TITLE FROM ' . $tableViewNameGenerator->getViewName(
            'addtest',
            0
        ) . " WHERE OXID = '" . $oxid . "'";
        $this->assertSame('some default title', oxDb::getDb()->getOne($sql));
    }

    /**
     * Verify that the expected data turned up in the language views
     */
    public function testViewContentsCreateAdditionalTableAfterCreatingLanguages(): void
    {
        //add nine more languages
        $languageId = $this->prepare(9);
        $oxid = '_test101';

        $this->createTable('addtest');
        $this->setConfigParam('aMultiLangTables', ['addtest']);

        $this->updateViews();

        //insert testdata for language id 0
        $sql = "INSERT INTO addtest (OXID, TITLE) VALUES ('" . $oxid . "', 'some default title')";
        oxDb::getDb()->execute($sql);

        //insert testdata for last added language id in set1 table
        $sql = 'INSERT INTO addtest_set1 (OXID, TITLE_' . $languageId . ") VALUES ('" . $oxid . "', 'some additional title')";
        oxDb::getDb()->execute($sql);

        $tableViewNameGenerator = oxNew(TableViewNameGenerator::class);
        $sql = 'SELECT TITLE FROM ' . $tableViewNameGenerator->getViewName(
            'addtest',
            $languageId
        ) . " WHERE OXID = '" . $oxid . "'";
        $this->assertSame('some additional title', oxDb::getDb()->getOne($sql));

        $sql = 'SELECT TITLE FROM ' . $tableViewNameGenerator->getViewName(
            'addtest',
            0
        ) . " WHERE OXID = '" . $oxid . "'";
        $this->assertSame('some default title', oxDb::getDb()->getOne($sql));
    }

    /**
     * Create additional multilanguage table.
     */
    private function createTable(string $name = 'addtest'): void
    {
        $sql = 'CREATE TABLE `' . $name . '` (' .
                "`OXID` char(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL COMMENT 'Item id'," .
                "`TITLE` varchar(128) NOT NULL DEFAULT '' COMMENT 'Title (multilanguage)'," .
                "`TITLE_1` varchar(128) NOT NULL DEFAULT ''," .
                "`TITLE_2` varchar(128) NOT NULL DEFAULT ''," .
                "`TITLE_3` varchar(128) NOT NULL DEFAULT ''," .
                "`TITLE_4` varchar(128) NOT NULL DEFAULT ''," .
                "`TITLE_5` varchar(128) NOT NULL DEFAULT ''," .
                "`TITLE_6` varchar(128) NOT NULL DEFAULT ''," .
                "`TITLE_7` varchar(128) NOT NULL DEFAULT ''," .
                'PRIMARY KEY (`OXID`)' .
                ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='for testing'";

        oxDb::getDb()->execute($sql);
        oxDb::getInstance()->getTableDescription($name); //throws exception if table does not exist
        $this->additionalTables[] = $name;
    }

    /**
     * Remove additional multilanguage tables and related.
     */
    private function removeAdditionalTables(string $name): void
    {
        $sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES  WHERE TABLE_NAME LIKE '%" . $name . "%'";
        $result = oxDb::getDb(oxDb::FETCH_MODE_ASSOC)->getAll($sql);
        foreach ($result as $sub) {
            oxDb::getDb()->execute('DROP TABLE IF EXISTS `' . $sub['TABLE_NAME'] . '`');
        }
    }

    private function setConfigParam(string $name, $value): void
    {
        Registry::getConfig()->setConfigParam($name, $value);
    }
}
