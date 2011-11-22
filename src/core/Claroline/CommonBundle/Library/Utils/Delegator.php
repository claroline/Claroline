<?php
namespace Claroline\CommonBundle\Library\Utils;
use \ReflectionClass;

class Delegator
{
    /** delegated object */
    protected $delegate;
    
    public function __construct($delegate) {
        $this->delegate = $delegate;
    }
    
    public function getDelegate() {
        return $this->delegate;
    }
    
    function __call($name, $args) {
        $r = new ReflectionClass($this);
        if ($r->hasMethod($name))
        {
            $method = $r->getMethod($name);
            if ($method->isPublic() && !$method->isAbstract()) {
                    call_user_func_array(array($this,$name), $args);
            }
        }
        return call_user_func_array(array($this->delegate,$name), $args);
    }
    
}
