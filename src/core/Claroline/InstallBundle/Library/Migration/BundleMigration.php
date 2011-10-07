<?php
namespace Claroline\InstallBundle\Library\Migration;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Table;

abstract class BundleMigration extends AbstractMigration
{
    /* @var MigrationHelper */
    private $helper;
    
    
    private function getHelper()
    {
        if($this->helper === null)
        {
            $this->helper = new MigrationHelper(); 
        }
        return $this->helper;
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
    
    /** helper function to add a reference column to a table */
    protected function addReference(Table $table, $ref, $nullable= false)
    {
        $table->addColumn(
            $ref . '_id',
            'integer',
            array(
                'notnull' => !$nullable
            )
        );
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
}