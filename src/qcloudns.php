<?php
declare(strict_types=1);
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;

namespace QCloudns;


class Client {
    private string $authId;
    private string $authPassword;
    private string $baseUrl = 'https://api.cloudns.net/dns/';
    private HttpClient $httpClient;

    // Define constants for endpoints
    private const ENDPOINT_GET_ACCOUNT_INFO = 'get-account-info.json';
    private const ENDPOINT_ADD_ZONE = 'register.json';
    private const ENDPOINT_DELETE_ZONE = 'delete.json';
    private const ENDPOINT_LIST_ZONES = 'list-zones.json';
    private const ENDPOINT_UPDATE_ZONE = 'update-zone.json';
    private const ENDPOINT_STATS_ZONE = 'get-zones-stats.json';
    
    
    
    private const ENDPOINT_ADD_RECORD = 'add-record.json';


    public function __construct(string $authId, string $authPassword) {
        $this->authId = $authId;
        $this->authPassword = $authPassword;
        $this->httpClient = new HttpClient(['base_uri' => $this->baseUrl]);
    }

    /**
     * Make an HTTP request to the API.
     *
     * @param string $endpoint The API endpoint.
     * @param array $params The query parameters.
     * @param string $method The HTTP method (default: GET).
     * @return array The decoded JSON response.
     * @throws \Exception If the request fails.
     */
    private function request(string $endpoint, array $params = [], string $method = 'GET'): array {
        $params['auth-id'] = $this->authId;
        $params['auth-password'] = $this->authPassword;

        try {
            $options = [
                'query' => $method === 'GET' ? $params : [],
                'form_params' => $method === 'POST' ? $params : [],
            ];

            $response = $this->httpClient->request($method, $endpoint, $options);
            $decodedResponse = json_decode($response->getBody()->getContents(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('JSON decode error: ' . json_last_error_msg());
            }

            return $decodedResponse;
        } catch (GuzzleException $e) {
            throw new \Exception('HTTP request error: ' . $e->getMessage());
        }
    }

   
    /**
     * Create a new DNS zone.
     *
     * @param string $domain The domain name.
     * @param string $zoneType The zone type (default: master).
     * @return array The response from the API.
     */
    public function createZone(string $domain, string $zoneType = 'master'): array {
        return $this->request(self::ENDPOINT_ADD_ZONE, [
            'domain-name' => $domain,
            'zone-type' => $zoneType,
        ], 'POST');
    }
    /**
     * Delete a  DNS zone.
     *
     * @param string $domain The domain name.
     * @return array The response from the API.
     */
    public function deleteZone(string $domain,): array {
        return $this->request(self::ENDPOINT_DELETE_ZONE, [
            'domain-name' => $domain,
        ], 'POST');
    }
    /**
     * List DNS zones.
     *
     * @param string $page.
     * @return array The response from the API.
     */
    public function listZones(string $page,): array {
        return $this->request(self::ENDPOINT_LIST_ZONES, [
            'page' => 1,
            'rows-per-page' => 10,
        ], 'POST');
    }
    /**
     * Gets the number of the zones you have and the zone limit of your customer plan. 
     * Reverse zones are included.
     *
     * 
     * @return array The response from the API.
     */
    public function statsZones(string $page,): array {
        return $this->request(self::ENDPOINT_STATS_ZONE,'POST');
    }

    /**
     * Update a  DNS zone.
     *
     * @param string $domain The domain name.
     * @return array The response from the API.
     */
    public function updateZone(string $domain,): array {
        return $this->request(self::ENDPOINT_UPDATE_ZONE, [
            'domain-name' => $domain,
        ], 'POST');
    }

    /**
     * Add a DNS record.
     *
     * @param string $domain The domain name.
     * @param string $recordType The record type.
     * @param string $host The host.
     * @param string $record The record value.
     * @param int $ttl The time to live (default: 3600).
     * @return array The response from the API.
     */
    public function addRecord(string $domain, string $recordType, string $host, string $record, int $ttl = 3600): array {
        return $this->request(self::ENDPOINT_ADD_RECORD, [
            'domain-name' => $domain,
            'record-type' => $recordType,
            'host' => $host,
            'record' => $record,
            'ttl' => $ttl,
        ], 'POST');
    }
}
