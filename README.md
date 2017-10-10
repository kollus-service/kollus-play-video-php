# Kollus Play Video By PHP

Play or download video by Kollus WebToken : Sample Source

## Requirement

* [php](http://php.net) : 5.5 above
   * module
      * [slimphp](https://www.slimframework.com/) : for sample code's web framework
      * [slim php-view](https://github.com/slimphp/PHP-View)
      * [guzzle php http client](http://docs.guzzlephp.org/)
      * [firebase/php-jwt](https://github.com/firebase/php-jwt)
* [jQuery](https://jquery.com) : 3.2.1
* [Boostrap 3](https://getbootstrap.com/docs/3.3/) : for sample code
      
## Installation

```bash
git clone https://github.com/kollus-service/kollus-play-video-php
cd kollus-play-video-php

composer install
```
Copy .config.yml to config.yml and Edit this.

```yaml
kollus:
  domain: [kollus domain]
  version: 0
  service_account:
    key : [service account key]
    api_access_token: [api access token]
    custom_key: [custom key]
    security_key: [security key]
  play_options:
    expire_time: 86400 # 1day
```

## How to use

```bash
composer start

...
> php -S 0.0.0.0:8080 -t public public/index.php
```

Open browser '[http://localhost:8080](http://localhost:8080)'

## Supported php version issue

Best way is using php7.

If you use php5.5 below, see [Alternative Ways](ALTERNATIVE.md)
* can't use jwt library by composer
* can't use json library
* can't use hash_hmac
* JWT Webtoken Sample Codes for php5.5 below

## Development flow

### Play video

1. Press 'Play' button and call local server api for generate 'web-token-url' on browser
   * '/auth/play-video-url/{channel_key}/{upload_file_key}' in public/index.php
2. Generate WebTokenURL
   * use getWebTokenURLByMediaContentKey in src/Component/Client/VideoGatewayClient.php
   * use getWebTokenByMediaItems in src/Component/Client/VideoGatewayClient.php
3. Open iframe + web-token-url in instant modal
   * use modal-play-video event in public/js/default.js
4. If you want... Kollus Player App can use 'kollus play callback'

### Download video

0. You must install Kollus Player App.
1. Press 'Download' button and call local server api for generate 'web-token-url' on browser
   * '/auth/download-video-url/{channel_key}/{upload_file_key}' in public/index.php
2. Generate WebTokenURL
   * use getWebTokenURLByMediaContentKey in src/Component/Client/VideoGatewayClient.php
   * use getWebTokenByMediaItems in src/Component/Client/VideoGatewayClient.php
3. Open iframe + web-token-url in instant modal
   * use modal-download-video event in public/js/default.js
4. Call Kollus Player App
5. If you want... Kollus Player App can use 'kollus drm callback'
6. If your platform is mac osx or media is not encrypted, it will be streaming.

### Play video playlist

0. You must install Kollus Player App.
1. Select video.
2. Press 'Play playlist' button and call local server api for generate 'web-token-url' on browser
   * '/auth/play-video-playlist/{channel_key}' in public/index.php
3. Generate WebTokenURL
   * use getWebTokenURLByMediaItems in src/Component/Client/VideoGatewayClient.php
   * use getWebTokenByMediaItems in src/Component/Client/VideoGatewayClient.php
4. Call Kollus Player App
5. If your platform is mac osx or more environments, is not working.

### Download Multi video

0. You must install Kollus Player App.
1. Select video.
2. Press 'Download selected' button and call local server api for generate 'web-token-url' on browser
   * '/auth/download-multi-video/{channel_key}' in public/index.php
3. Generate WebTokenURL
   * use getWebTokenURLByMediaItems in src/Component/Client/VideoGatewayClient.php
   * use getWebTokenByMediaItems in src/Component/Client/VideoGatewayClient.php
4. Call Kollus Player App


### Important code

#### Common library

src/Client/VideoGatewayClient.php
```php
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
```

#### Play video

public/index.php

```php
$app->post(
    '/auth/play-video-url/{channel_key}/{upload_file_key}',
    function (Request $request, Response $response, $args) use ($container) {

        ...

        $channelKey = isset($args['channel_key']) ? $args['channel_key'] : null;
        $uploadFileKey = isset($args['upload_file_key']) ? $args['upload_file_key'] : null;

        $mediaContent = $kollusApiClient->getChannelMediaContent($channelKey, $uploadFileKey);

        $data = [
            'title' => $mediaContent->getTitle(),
            'web_token_url' => $kollusVideoGatewayClient->getWebTokenURLByMediaContentKey(
                # get media-content-key by your DB
                $mediaContent->getMediaContentKey(),
                $clientUserId,
                [ 'expire_time' => $kollusSettings['play_options']['expire_time'], 'autoplay' => true ]
            ),
        ];

        return $response->withJson($data, 200);
    }
)->setName('auth-play-video-url');
```

public/js/default.js

```javascript
$(document).on('click', 'button[data-action=modal-play-video]', function(e) {
  e.preventDefault();

  ...

  $.post('/auth/play-video-url/' + channelKey + '/' + uploadFileKey, function (data) {
    modalContent = $(
      
      ...
      
      '        <iframe src="' + data.web_token_url + '" class="embed-responsive-item" allowfullscreen></iframe>\n' +
      
      ...
    );

    showModal(modalContent)
  });
});
```

#### Download video

public/index.php

```php
$app->post(
    '/auth/download-video-url/{channel_key}/{upload_file_key}',
    function (Request $request, Response $response, $args) use ($container) {

        ...

        $channelKey = isset($args['channel_key']) ? $args['channel_key'] : null;
        $uploadFileKey = isset($args['upload_file_key']) ? $args['upload_file_key'] : null;

        $mediaContent = $kollusApiClient->getChannelMediaContent($channelKey, $uploadFileKey);

        $data = [
            'title' => $mediaContent->getTitle(),
            'web_token_url' => $kollusVideoGatewayClient->getWebTokenURLByMediaContentKey(
                # get media-content-key by your DB
                $mediaContent->getMediaContentKey(),
                $clientUserId,
                [ 'expire_time' => $kollusSettings['play_options']['expire_time'], 'download' => true ]
            ),
        ];

        return $response->withJson($data, 200);
    }
)->setName('auth-download-video-url');
```

public/js/default.js

```javascript
$(document).on('click', 'button[data-action=modal-download-video]', function(e) {
  e.preventDefault();

  ...

  $.post('/auth/download-video-url/' + channelKey + '/' + uploadFileKey, function (data) {
    modalContent = $(

      ...

      '        <iframe src="' + data.web_token_url + '" class="embed-responsive-item" allowfullscreen></iframe>\n' +

      ...

    );

    showModal(modalContent)
  });
});

````

#### Play video by playlist

public/index.php

```php
$app->post(
    '/auth/play-video-playlist/{channel_key}',
    function (Request $request, Response $response, $args) use ($container) {

        ...

        $channelKey = isset($args['channel_key']) ? $args['channel_key'] : null;
        $selectedMediaItems = $request->getParam('selected_media_items', []);

        $mediaItems = new Kollus\Component\Container\ContainerArray();
        foreach ($selectedMediaItems as $selectedMediaItem) {
            $mediaContent = $kollusApiClient->getChannelMediaContent(
                $channelKey,
                $selectedMediaItem['upload_file_key']
            );
            $mediaItems->append(
                new \Kollus\Component\Container\MediaItem([
                    # get media-content-key by your DB
                    'media_content_key' => $mediaContent->getMediaContentKey()
                ])
            );
        }

        $data = [
            'web_token_url' => $kollusVideoGatewayClient->getWebTokenURLByMediaItems(
                $mediaItems,
                $clientUserId,
                [ 'kind' => 'si', 'expire_time' => $kollusSettings['play_options']['expire_time'], 'autoplay' => true ]
            ),
        ];

        return $response->withJson($data, 200);
    }
)->setName('auth-play-video-playlist');
```

public/js/default.js

```javascript
$(document).on('click', 'button[data-action=call-play-video-playlist]', function(e) {
  e.preventDefault();

  ...

  checkedItems.each(function(index, element) {
    uploadFileKey = $(element).val();

    postDatas.selected_media_items.push({
      upload_file_key: uploadFileKey
    });
  });

  $.post('/auth/play-video-playlist/' + channelKey, postDatas, function (data) {
    document.location.href = 'kollus://path?url=' + encodeURIComponent(data.web_token_url);
  });
});
```

#### Download multi video

public/index.php

```php
$app->post(
    '/auth/download-multi-video/{channel_key}',
    function (Request $request, Response $response, $args) use ($container) {

        ...

        $channelKey = isset($args['channel_key']) ? $args['channel_key'] : null;
        $selectedMediaItems = $request->getParam('selected_media_items', []);

        $mediaItems = new Kollus\Component\Container\ContainerArray();
        foreach ($selectedMediaItems as $selectedMediaItem) {
            $mediaContent = $kollusApiClient->getChannelMediaContent(
                $channelKey,
                $selectedMediaItem['upload_file_key']
            );
            $mediaItems->append(
                new \Kollus\Component\Container\MediaItem([
                    # get media-content-key by your DB
                    'media_content_key' => $mediaContent->getMediaContentKey()
                ])
            );
        }

        $data = [
            'web_token_url' => $kollusVideoGatewayClient->getWebTokenURLByMediaItems(
                $mediaItems,
                $clientUserId,
                [ 'kind' => 'si', 'expire_time' => $kollusSettings['play_options']['expire_time'] ]
            ),
        ];

        return $response->withJson($data, 200);
    }
)->setName('auth-download-multi-video');
```

public/js/default.js

```javascript
$(document).on('click', 'button[data-action=call-download-multi-video]', function(e) {
  e.preventDefault();

  ...

  checkedItems.each(function(index, element) {
    uploadFileKey = $(element).val();

    postDatas.selected_media_items.push({
      upload_file_key: uploadFileKey
    });
  });

  $.post('/auth/download-multi-video/' + channelKey, postDatas, function (data) {
    document.location.href = 'kollus://download?url=' + encodeURIComponent(data.web_token_url);
  });
});
```

## License

See `LICENSE` for more information
