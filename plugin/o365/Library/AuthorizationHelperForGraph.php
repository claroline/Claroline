<?php

namespace FormaLibre\OfficeConnectBundle\Library;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("formalibre.office_connect.library.authorization_helper_for_graph")
 */
 // A class that provides authortization token for apps that need to access Azure Active Directory Graph Service.
class AuthorizationHelperForGraph
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
     * Get Authorization URL.
     */
    public function getAuthorizatonURL()
    {
        $authUrl = 'https://login.windows.net/common/oauth2/authorize'.'?'.
           'response_type=code'.'&'.
           'client_id='.$this->settings->getClientId().'&'.
           'resource='.$this->settings->getResourceUri().'&'.
           'redirect_uri='.$this->settings->getRedirectUri();

        return $authUrl;
    }

    /**
     * Use the code retrieved from authorization URL to get the authentication token that will be used to talk to Graph.
     */
    public function getAuthenticationHeaderFor3LeggedFlow($code)
    {
        // Construct the body for the STS request
        $authenticationRequestBody = 'grant_type=authorization_code'.'&'.
            'client_id='.urlencode($this->settings->getClientId()).'&'.
            'redirect_uri='.$this->settings->getRedirectUri().'&'.
            'client_secret='.urlencode($this->settings->getPassword()).'&'.
            'code='.$code;

        $ch = curl_init();
        $stsUrl = 'https://login.windows.net/common/oauth2/token';
        curl_setopt($ch, CURLOPT_URL, $stsUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,  $authenticationRequestBody);
        // By default, HTTPS does not work with curl.
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $output = curl_exec($ch);
        curl_close($ch);
        $tokenOutput = json_decode($output);
        $tokenType = $tokenOutput->{'token_type'};
        $accessToken = $tokenOutput->{'access_token'};
        $tokenScope = $tokenOutput->{'scope'};
        //this is dirty but wontfix for now
        $_SESSION['token_type'] = $tokenType;
        $_SESSION['access_token'] = $accessToken;
        $_SESSION['tokenOutput'] = $tokenOutput;
    }
}
