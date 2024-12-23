<?php

namespace common\helpers;

use Shopify\Auth\FileSessionStorage;
use Shopify\Clients\HttpResponse;
use Shopify\Context;
use Shopify\Clients\Graphql;
use Shopify\Exception\MissingArgumentException;
use Yii;
use yii\base\Component;
use yii\base\Exception;

class ShopifyClient extends Component
{
    public $storeUrl;
    public $accessToken;
    public $shopifyApiKey;
    public $shopifySecretKey;
    public $shopifyScopes;
    public $apiVersion;

    private $client;

    /**
     * Initializes the Shopify client with store URL and access token.
     *
     * @throws MissingArgumentException
     */
    public function init()
    {
        parent::init();

        Context::initialize(
            apiKey: $this->shopifyApiKey,
            apiSecretKey: $this->shopifySecretKey,
            scopes: $this->shopifyScopes,
            hostName: $this->storeUrl,
            sessionStorage: new FileSessionStorage(Yii::getAlias('@runtime') . '/shopify_sessions'),
            apiVersion: $this->apiVersion,
            isEmbeddedApp: true,
            isPrivateApp: false
        );

        $this->client = new Graphql($this->storeUrl, $this->accessToken);
    }

    /**
     * @param string $query
     * @param array $variables
     * @return array
     * @throws Exception
     */
    public function sendQuery(string $query, array $variables = []): array
    {
        try {
            $response = $this->client->query($query, $variables);
            return $this->processResponse($response);
        } catch (\Exception $e) {
            throw new Exception('Error sending query to Shopify API: ' . $e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    public function sendMutation(string $mutation): array
    {
        try {
            $response = $this->client->query($mutation);
            return $this->processResponse($response);
        } catch (\Exception $e) {
            throw new Exception('Error sending mutation to Shopify API: ' . $e->getMessage());
        }
    }

    /**
     * @param HttpResponse $response
     * @return array
     * @throws Exception
     */
    private function processResponse(HttpResponse $response): array
    {
        if ($response instanceof HttpResponse) {
            $body = $response->getBody();
            $data = json_decode($body, true);

            if (isset($data['errors'])) {
                throw new Exception('GraphQL error: ' . json_encode($data['errors']));
            }

            return $data['data'];
        } else {
            throw new Exception('Unexpected response format');
        }
    }

}
