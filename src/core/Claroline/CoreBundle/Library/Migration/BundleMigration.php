<?php

namespace Claroline\CoreBundle\Library\Migration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Table;
use Claroline\CoreBundle\Exception\ClarolineException;

abstract class BundleMigration extends AbstractMigration
{
    /** MigrationHelper */
    private $helper;
    
    /** array of Doctrine\DBAL\Schema\Table */
    private $storedTables = array();
    
    /**
     * Helper method storing a table in a private attribute to facilitate
     * its retrieval in another context (helps for relationships).
     * 
     * @param Table $table
     */
    protected function storeTable(Table $table)
    {
        $this->storedTables[$table->getName()] = $table;
    }
    
    /**
     * Helper method returning a table previously stored using
     * the BundleMigration::storeTable() method.
     * 
     * @param string $tableName
     * @return Table 
     */
    protected function getStoredTable($tableName)
    {
        if (! isset($this->storedTables[$tableName]))
        {
            throw new ClarolineException("Unknown table '{$tableName}'.");
        }
        
        return $this->storedTables[$tableName];
    }
    
    protected function prefix()
    {        
        $helper = $this->getHelper();
        
        return $helper->getTablePrefixForMigration($this);        
    }
    
    /** helper function to add a autoincremented column to a table */
    protected function addId(Table $table, $generated = true)
    {
        $table->addColumn(
            'id',
            'integer',
            array(
                'autoincrement' => $generated,
            )
        );
        $table->setPrimaryKey(array('id'));
    }
    
    /** helper function to add a discriminator column to a table */
    protected function addDiscriminator(Table $table, $discriminator = 'discr')
    {
        $table->addColumn(
            $discriminator,
            'string',
            array('length' => 255)            
        );
    }
      
    private function getHelper()
    {
        if ($this->helper === null)
        {
            $this->helper = new MigrationHelper(); 
        }
        
        return $this->helper;
    }
}