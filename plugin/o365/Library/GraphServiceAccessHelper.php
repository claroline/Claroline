<?php

namespace FormaLibre\OfficeConnectBundle\Library;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("formalibre.office_connect.library.graph_service_access_helper")
 */
class GraphServiceAccessHelper
{
    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "settings" = @DI\Inject("formalibre.office_connect.library.settings"),
     * })
     */
    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Constructs a Http GET request to fetch the entry for the current user.
     * Returns the json decoded respone as the objects that were recieved in feed.
     */
    public function getMeEntry()
    {
        $ch = curl_init();
        // Add authorization and other headers. Also set some common settings.
        $this->addRequiredHeadersAndSettings($ch);
        $feedURL = 'https://graph.windows.net/'.$this->settings->getAppTenantDomainName().'/me/';
        $feedURL = $feedURL.'?'.$this->settings->getApiVersion();
        curl_setopt($ch, CURLOPT_URL, $feedURL);
        $output = curl_exec($ch);
        curl_close($ch);
        $jsonOutput = json_decode($output);

        return $jsonOutput;
    }

    /**
     * Add required headers like Authorization, Accept, Content-Type etc.
     */
    private function addRequiredHeadersAndSettings($ch)
    {
        $authHeader = 'Authorization:'.$_SESSION['token_type'].' '.$_SESSION['access_token'];
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            $authHeader, 'Accept:application/json;odata=minimalmetadata',
           'Content-Type:application/json', ]
       );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // By default https does not work for CURL.
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    }
}
