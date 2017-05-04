<?php

namespace HeVinci\UrlBundle\Manager;

use Claroline\CoreBundle\Persistence\ObjectManager;
use HeVinci\UrlBundle\Entity\Url;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @DI\Service("hevinci_url.manager.url")
 */
class UrlManager
{
    protected $objectManager;
    protected $request;

    /**
     * @DI\InjectParams({
     *      "objectManager" = @DI\Inject("claroline.persistence.object_manager"),
     *      "request_stack" = @DI\Inject("request_stack")
     * })
     */
    public function __construct(
        ObjectManager $objectManager,
        RequestStack $requestStack
    ) {
        $this->objectManager = $objectManager;
        $this->request = $requestStack;
    }

    public function setUrl(Url $url)
    {
        $address = $url->getUrl();
        $baseUrlEscapeQuote = null;
        $baseUrl = null;
        $url->setInternalUrl(false);

        if ($this->request->getCurrentRequest() !== null) {
            $baseUrl = $this->request->getCurrentRequest()->getSchemeAndHttpHost().$this->request->getCurrentRequest()->getScriptName();
            $baseUrlEscapeQuote = preg_quote($baseUrl);

            if (preg_match("#$baseUrlEscapeQuote#", $address)) {
                $url->setUrl(substr($address, strlen($baseUrl)));
                $url->setInternalUrl(true);
            }
        }

        return $url;
    }
}
