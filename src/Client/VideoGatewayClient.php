<?php

namespace Kollus\Component\Client;

use GuzzleHttp\Client as HttpClient;
use Kollus\Component\Container;
use Firebase\JWT\JWT;

class VideoGatewayClient extends AbstractClient
{
    /**
     * @var HttpClient $client
     */
    protected $client;

    public function connect($client = null)
    {
        if (is_subclass_of($this->serviceAccount, Container\ServiceAccount::class)) {
            throw new ClientException('Service account is required.');
        }

        $serviceAccountKey = $this->serviceAccount->getSecurityKey();
        if (empty($serviceAccountKey)) {
            throw new ClientException('Security key is empty.');
        }

        if (!is_null($client)) {
            $this->client = $client;
        }

        return $this;
    }

    public function disconnect()
    {
        unset($this->client);
        return $this;
    }

    /**
     * @return string
     */
    public function getVideoGateWayDomain()
    {
        return 'v.' . $this->domain;
    }

    /**
     * @param string $mediaContentKey
     * @param string|null $clientUserId
     * @param array $optParams
     * @return string
     * @throws ClientException
     */
    public function getWebTokenByMediaContentKey($mediaContentKey, $clientUserId = null, array $optParams = [])
    {
        $mediaProfileKey = isset($optParams['media_profile_key']) ? $optParams['media_profile_key'] : null;;
        $isIntro = isset($optParams['is_intro']) ? $optParams['is_intro'] : null;
        $isSeekable = isset($optParams['is_seekable']) ? $optParams['is_seekable'] : null;
        $seekableEnd = isset($optParams['seekable_end']) ? $optParams['seekable_end'] : null;
        $disablePlayrate = isset($optParams['disable_playrate']) ? $optParams['disable_playrate'] : null;

        $mediaItem = [ 'media_content_key' => $mediaContentKey ];

        if (!is_null($mediaProfileKey)) {
            $mediaItem['media_profile_key'] = $mediaProfileKey;
        }

        if (!is_null($isIntro)) {
            $mediaItem['is_intro'] = (int)$isIntro;
        }

        if (!is_null($isSeekable)) {
            $mediaItem['is_seekable'] = (int)$isSeekable;
        }

        if (!is_null($seekableEnd)) {
            $mediaItem['seekable_end'] = $seekableEnd;
        }

        if (!is_null($disablePlayrate)) {
            $mediaItem['disable_playrate'] = (int)$disablePlayrate;
        }

        $mediaItems = new Container\ContainerArray();
        $mediaItems->append(new Container\MediaItem($mediaItem));

        return $this->getWebTokenByMediaItems($mediaItems, $clientUserId, $optParams);
    }

    /**
     * @param Container\MediaItem[]|Container\ContainerArray $mediaItems
     * @param string|null $clientUserId
     * @param array $optParams
     * @return string
     * @throws ClientException
     */
    public function getWebTokenByMediaItems($mediaItems, $clientUserId = null, array $optParams = []) {
        $securityKey = isset($optParams['security_key']) ?
            $optParams['security_key'] : $this->serviceAccount->getSecurityKey();
        $mediaProfileKey = isset($optParams['media_profile_key']) ? $optParams['media_profile_key'] : null;
        $awtCode = isset($optParams['awt_code']) ? $optParams['awt_code'] : null;
        $expireTime = isset($optParams['expire_time']) ? (int)$optParams['expire_time'] : 7200;

        $payload = (object)[];
        $payload->mc = [];

        foreach ($mediaItems as $mediaItem) {
            /** @var Container\MediaItem $mediaItem */
            if ($mediaItem instanceof Container\MediaItem) {
                $mcClaim = (object) [];

                if (empty($mediaItem->getMediaContentKey())) {
                    throw new ClientException('MediaItem is invalid');
                } else {
                    $mcClaim->mckey = $mediaItem->getMediaContentKey();
                }

                if (!is_null($mediaProfileKey)) {
                    $mcClaim->mcpf = $mediaProfileKey;
                } else {
                    if (!empty($mediaItem->getProfileKey())) {
                        $mcClaim->mcpf = $mediaItem->getProfileKey();
                    }
                }

                if (!empty($mediaItem->isIntro())) {
                    $mcClaim->intr = (int)$mediaItem->isIntro();
                }

                if (!empty($mediaItem->isSeekable())) {
                    $mcClaim->seek = (int)$mediaItem->isSeekable();
                }

                if (!empty($mediaItem->getSeekableEnd())) {
                    $mcClaim->seekable_end = $mediaItem->getSeekableEnd();
                }

                if (!empty($mediaItem->isDisablePlayrate())) {
                    $mcClaim->disable_playrate = (int)$mediaItem->isDisablePlayrate();
                }

                $payload->mc[] = $mcClaim;
            }
        }

        if (!empty($clientUserId)) {
            $payload->cuid = $clientUserId;
        }

        if (!is_null($awtCode)) {
            $payload->awtc = $awtCode;
        }

        if (!empty($expireTime)) {
            $payload->expt = time() + $expireTime;
        }

        return JWT::encode($payload, $securityKey);
    }

    /**
     * @param string $mediaContentKey
     * @param string|null $clientUserId
     * @param array $optParams
     * @param array $getParams
     * @return string
     */
    public function getWebTokenURLByMediaContentKey($mediaContentKey, $clientUserId = null, array $optParams = [], array $getParams = [])
    {
        $modePath = isset($optParams['kind']) && !empty($optParams['kind']) ? $optParams['kind'] : 's';

        if (isset($optParams['download'])) {
            $getParams['download'] = true;
            $getParams['force_exclusive_player'] = true;
        } else {
            if (isset($optParams['autoplay'])) {
                $getParams['autoplay'] = true;
            }

            if (isset($optParams['mute'])) {
                $getParams['mute'] = true;
            }
        }

        $getParams['jwt'] = $this->getWebTokenByMediaContentKey($mediaContentKey, $clientUserId, $optParams);
        $getParams['custom_key'] = $this->serviceAccount->getCustomKey();

        $queryString = '';
        if (count($getParams) > 0) {
            $queryString = http_build_query($getParams);
            if (!empty($queryString)) {
                $queryString = '?' . $queryString;
            }
        }

        return $this->getSchema() . '://' . $this->getVideoGateWayDomain() . '/' . $modePath . $queryString;
    }

    /**
     * @param Container\MediaItem[]|Container\ContainerArray $mediaItems
     * @param string|null $clientUserId
     * @param array $optParams
     * @param array $getParams
     * @return string
     */
    public function getWebTokenURLByMediaItems($mediaItems, $clientUserId = null, array $optParams = [], array $getParams = [])
    {
        $modePath = isset($optParams['kind']) && !empty($optParams['kind']) ? $optParams['kind'] : 's';

        if (isset($optParams['download'])) {
            $getParams['download'] = true;
            $getParams['force_exclusive_player'] = true;
        } else {
            if (isset($optParams['autoplay'])) {
                $getParams['autoplay'] = true;
            }

            if (isset($optParams['mute'])) {
                $getParams['mute'] = true;
            }
        }

        $getParams['jwt'] = $this->getWebTokenByMediaItems($mediaItems, $clientUserId, $optParams);
        $getParams['custom_key'] = $this->serviceAccount->getCustomKey();

        $queryString = '';
        if (count($getParams) > 0) {
            $queryString = http_build_query($getParams);
            if (!empty($queryString)) {
                $queryString = '?' . $queryString;
            }
        }

        return $this->getSchema() . '://' . $this->getVideoGateWayDomain() . '/' . $modePath . $queryString;
    }
}
