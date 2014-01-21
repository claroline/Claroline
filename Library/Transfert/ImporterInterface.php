<?php


namespace Claroline\CoreBundle\Library\Transfert;


interface ImporterInterface {

    public function supports($type);

    public function valid(\DOMNodeList $node);

    public function import( $objects);

} 