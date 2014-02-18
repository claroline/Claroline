<?php


namespace Claroline\CoreBundle\Library\Transfert;


interface ImporterInterface {

    /**
     * @param string $type support format ex: yml,xml
     * @return boolean
     */
    public function supports($type);

    /**
     * check if the key of the importer are in the array given
     * @param array $array
     * @return boolean
     */
    public function validate(array $array);

    /**
     * Data from the manifest file
     * @param  array $array
     * @return boolean
     */
    public function import(array $array);

} 