<?php

namespace Kollus\Component\Client;

use GuzzleHttp\Client as HttpClient;
use Kollus\Component\Container;

class ApiClient extends AbstractClient
{
    /**
     * @var HttpClient $client
     */
    protected $client;

    /**
     * @param string $method
     * @param string $uri
     * @param array $optParams
     * @param array $postParams
     * @param array $getParams
     * @param int $retry
     * @return mixed
     * @throws ClientException
     */
    private function getResponseJSON(
        $method,
        $uri,
        array $getParams = [],
        array $postParams = [],
        array $optParams = [],
        $retry = 3
    ) {
        if (count($getParams) > 0) {
            $optParams['query'] = $getParams;
        }

        if (count($postParams) > 0) {
            $optParams['form_params'] = $postParams;
        }

        if (!isset($optParams['timeout'])) {
            $optParams['timeout'] = $this->optParams['timeout'];
        }

        do {
            $response = $this->client->request($method, $uri, $optParams);

            $statusCode = $response->getStatusCode();
            $retry --;
        } while ($statusCode != 200 && $retry > 0);

        $jsonResponse = json_decode($response->getBody()->getContents());
        if ($jsonResponse === false || (isset($jsonResponse->error) && (int)$jsonResponse->error === 1)) {
            $message = isset($jsonResponse->message) ? $jsonResponse->message : $response->getBody()->getContents();
            throw new ClientException($message, $statusCode);
        }

        return $jsonResponse;
    }

    /**
     * @param mixed|null $client
     * @return self
     * @throws ClientException
     */
    public function connect($client = null)
    {
        if (is_subclass_of($this->serviceAccount, Container\ServiceAccount::class)) {
            throw new ClientException('Service account is required.');
        }

        $serviceAccountKey = $this->serviceAccount->getKey();
        if (empty($serviceAccountKey)) {
            throw new ClientException('Service account key is empty.');
        }

        $apiAccessToken = $this->serviceAccount->getApiAccessToken();
        if (empty($apiAccessToken)) {
            throw new ClientException('Access token is empty.');
        }

        if (is_null($client)) {
            $this->client = new HttpClient([
                'base_uri' => $this->schema . '://api.' . $this->domain . '/' . $this->version . '/',
                'defaults' => ['allow_redirects' => false],
                'verify' => false,
            ]);
        } else {
            $this->client = $client;
        }

        return $this;
    }

    /**
     * @return self
     * @throws ClientException
     */
    public function disconnect()
    {
        unset($this->client);
        return $this;
    }

    /**
     * @param array $getParams
     * @param bool|false $force
     * @return Container\ContainerArray
     * @throws ClientException
     */
    public function getChannels(array $getParams = [], $force = false)
    {
        $getParams['access_token'] = $this->serviceAccount->getApiAccessToken();
        $getParams['force'] = (int)$force;
        $response = $this->getResponseJSON('GET', 'media/channel', $getParams);

        if (isset($response->result->count) && isset($response->result->items)) {
            if (is_array($response->result->items)) {
                $items = $response->result->items;
            } elseif (isset($response->result->items->item) && is_array($response->result->items->item)) {
                $items = $response->result->items->item;
            } else {
                throw new ClientException('Response is invalid.');
            }
        } else {
            throw new ClientException('Response is invalid.');
        }

        $channels = new Container\ContainerArray();
        foreach ($items as $item) {
            $channels->appendElement(new Container\Channel($item));
        }

        return $channels;
    }

    /**
     * @param string $channelKey
     * @param int $page
     * @param array $getParams
     * @param bool|false $force
     * @return object
     * @throws ClientException
     */
    public function findChannelMediaContentsByPage($channelKey, $page = 1, array $getParams = [], $force = false)
    {
        $getParams['channel_key'] = $channelKey;
        $getParams['access_token'] = $this->serviceAccount->getApiAccessToken();
        $getParams['page'] = $page;
        $getParams['force'] = (int)$force;
        $response = $this->getResponseJSON('GET', 'media/channel/media_content.json', $getParams);

        if (isset($response->result->count) && isset($response->result->items)) {
            if (is_array($response->result->items)) {
                $items = $response->result->items;
            } elseif (isset($response->result->items->item) && is_array($response->result->items->item)) {
                $items = $response->result->items->item;
            } else {
                throw new ClientException('Response is invalid.');
            }
        } else {
            throw new ClientException('Response is invalid.');
        }

        $mediaContents = new Container\ContainerArray();
        foreach ($items as $item) {
            $mediaContents->appendElement(new Container\MediaContent($item));
        }

        return (object)[
            'per_page' => $response->result->per_page,
            'count' => $response->result->count,
            'items' => $mediaContents,
        ];
    }

    /**
     * @param string $channelKey
     * @param string $uploadFileKey
     * @param bool $force
     * @return Container\MediaContent|null
     * @throws ClientException
     */
    public function getChannelMediaContent($channelKey, $uploadFileKey, $force = false)
    {
        $getParams = [
            'access_token' => $this->serviceAccount->getApiAccessToken(),
            'channel_key' => $channelKey,
            'force' => (int)$force,
        ];
        $response = $this->getResponseJSON(
            'GET',
            'media/channel/media_content/' . $uploadFileKey . '.json',
            $getParams
        );

        if (!isset($response->result->item)) {
            throw new ClientException('Response is invalid.');
        }

        return new Container\MediaContent($response->result->item);
    }
}
