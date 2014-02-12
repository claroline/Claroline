<?php


namespace Claroline\CoreBundle\Library\Transfert;


interface ImporterInterface {

    public function supports($type);

    public function valid($array);

    public function import( $objects);

} 