<?php

namespace Claroline\CoreBundle\Library\Installation;

use \InvalidArgumentException;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Table;

abstract class BundleMigration extends AbstractMigration
{
    /** @var MigrationHelper */
    private $helper;

    /** @var array[Doctrine\DBAL\Schema\Table] */
    private $storedTables = array();

    /**
     * Helper method returning a default table prefix based on the bundle name.
     *
     * @return string prefix
     */
    protected function getTablePrefix()
    {
        $helper = $this->getHelper();

        return $helper->getTablePrefixForMigration($this);
    }

    /**
     * Helper method adding a autoincremented id column to a table.
     */
    protected function addId(Table $table, $generated = true)
    {
        $table->addColumn(
            'id', 'integer', array(
            'autoincrement' => $generated,
            )
        );
        $table->setPrimaryKey(array('id'));
    }

    /**
     * Helper method adding a discriminator column to a table
     * (needed for ORM inheritance).
     */
    protected function addDiscriminator(Table $table, $discriminator = 'discr')
    {
        $table->addColumn(
            $discriminator, 'string', array('length' => 255)
        );
    }

    /**
     * Helper method storing a table in a private attribute to facilitate
     * its retrieval in another context (helps for relationships).
     */
    protected function storeTable(Table $table)
    {
        $this->storedTables[$table->getName()] = $table;
    }

    /**
     * Helper method returning a table previously stored using
     * the BundleMigration::storeTable() method.
     *
     * @return Table
     */
    protected function getStoredTable($tableName)
    {
        if (!isset($this->storedTables[$tableName])) {
            throw new InvalidArgumentException("Unknown table '{$tableName}'.");
        }

        return $this->storedTables[$tableName];
    }

    private function getHelper()
    {
        if ($this->helper === null) {
            $this->helper = new MigrationHelper();
        }

        return $this->helper;
    }
}