<?php

namespace Icap\BibliographyBundle\Controller\API;

use Claroline\AppBundle\Persistence\ObjectManager;
use Icap\BibliographyBundle\Entity\BookReferenceConfiguration;
use Icap\BibliographyBundle\Repository\BookReferenceConfigurationRepository;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\Translation\TranslatorInterface;

class ApiController
{
    /** @var TranslatorInterface */
    private $translator;

    /** @var BookReferenceConfigurationRepository */
    private $configRepository;

    public function __construct(TranslatorInterface $translator, ObjectManager $om)
    {
        $this->translator = $translator;
        $this->configRepository = $om->getRepository(BookReferenceConfiguration::class);
    }

    /**
     * Search in ISBNDB.
     *
     * @return array
     *
     * @throws HttpException
     * @throws \Exception
     */
    public function bookSearchAction($paramFetcher)
    {
        $api_key = $this->getApiKey();

        // Prepare API URL, always ask for stats
        $query = $paramFetcher->get('query');
        $index = $paramFetcher->get('index');
        $page = $paramFetcher->get('page');
        $url = "http://isbndb.com/api/v2/json/$api_key/books?q=$query&i=$index&p=$page&opt=keystats";

        // Send request
        $result = $this->sendRequest($url);

        // Remove keyId if present
        unset($result['keystats']['key_id']);

        return $result;
    }

    /**
     * Get book details from ISBNDB.
     *
     * @param $paramFetcher
     */
    public function bookDetailsAction($paramFetcher)
    {
        $api_key = $this->getApiKey();

        // Prepare API URL, always ask for stats
        $bookId = $paramFetcher->get('bookId');
        $url = "http://isbndb.com/api/v2/json/$api_key/book/$bookId?opt=keystats";

        // Send request
        $result = $this->sendRequest($url);

        // Remove keyId if present
        unset($result['keystats']['key_id']);

        return $result;
    }

    /**
     * @return mixed
     *
     * @throws \Exception
     */
    private function getApiKey()
    {
        $config = $this->configRepository->findAll()[0];
        $api_key = $config->getApiKey();

        if (is_null($api_key)) {
            throw new \Exception($this->translator->trans('api_not_configured', [], 'icap_bibliography'));
        }

        return $api_key;
    }

    private function sendRequest($url)
    {
        $result = $this->curlRequest($url);

        if (array_key_exists('error', $result)) {
            if ($result['keystats']['member_use_requests'] >= ($result['keystats']['free_use_limit'] + $result['keystats']['daily_max_pay_uses'])) {
                // Too Many Requests
                throw new HttpException(429);
            } else {
                throw new HttpException(404, $result['error']);
            }
        }

        return $result;
    }

    /**
     * @param $url
     *
     * @return mixed
     *
     * @throws HttpException
     */
    private function curlRequest($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_COOKIESESSION, true);
        $data = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);

        if (200 !== $info['http_code']) {
            throw new HttpException($info['http_code']);
        }

        return json_decode($data, true);
    }
}
