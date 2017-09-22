<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';

use Slim\Http\Request;
use Slim\Http\Response;

session_start();

$kollusConfig = [];
$configFilePath = __DIR__ . '/../config.yml';
if (file_exists($configFilePath)) {
    $yamlParser = new \Symfony\Component\Yaml\Parser();
    $parser = $yamlParser->parse(file_get_contents($configFilePath));
    $kollusConfig = $parser['kollus'];
}

// Instantiate the app
$settings = [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header
        // Renderer settings
        'renderer' => [
            'template_path' => __DIR__ . '/../templates/',
        ],
        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],
        'kollus' => $kollusConfig,
    ],
];
$app = new \Slim\App($settings);

$container = $app->getContainer();

$container['kollusApiClient'] = function ($c) {
    $settings = $c->get('settings')['kollus'];

    $apiClient = null;
    if (isset($settings['domain']) &&
        isset($settings['version']) &&
        isset($settings['service_account']['key']) &&
        isset($settings['service_account']['api_access_token'])
    ) {
        // Get API Client
        $apiClient = new \Kollus\Component\Client\ApiClient($settings['domain'], $settings['version']);
        $serviceAccount = new \Kollus\Component\Container\ServiceAccount($settings['service_account']);
        $apiClient->setServiceAccount($serviceAccount);
        $apiClient->connect();
    }

    return $apiClient;
};

$container['kollusVideoGatewayClient'] = function ($c) {
    $settings = $c->get('settings')['kollus'];

    $videoGatewayClient = null;
    if (isset($settings['domain']) &&
        isset($settings['version']) &&
        isset($settings['service_account']['key']) &&
//        isset($settings['service_account']['security_key']) &&
        isset($settings['service_account']['custom_key'])
    ) {
        // Get API Client
        $videoGatewayClient = new \Kollus\Component\Client\VideoGatewayClient($settings['domain'], $settings['version']);
        $serviceAccount = new \Kollus\Component\Container\ServiceAccount($settings['service_account']);
        $videoGatewayClient->setServiceAccount($serviceAccount);
        $videoGatewayClient->connect();
    }

    return $videoGatewayClient;
};

// session
$container['session'] = function ($c) {
    return new \SlimSession\Helper();
};

// view renderer
$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// Routes
$app->get('/', function (Request $request, Response $response) use ($container) {
    $kollusSettings = $this->settings['kollus'];
    $session = $container->get('session');
    /** @var \SlimSession\Helper $session */

    $clientUserId = $session->get('client_user_id');
    $existsConfig = !empty($kollusSettings);

    if ($clientUserId) {
        return $response->withRedirect($this->router->pathFor('channel-index'));
    }

    $data = [
        'clientUserId' => $clientUserId,
        'existsConfig' => $existsConfig,
        'kollus' => $kollusSettings,
    ];

    return $this->renderer->render($response, 'index.phtml', $data);
})->setName('default-index');

$app->post('/', function (Request $request, Response $response) use ($container) {

    $session = $container->get('session');
    /** @var \SlimSession\Helper $session */

    $clientUserId = $request->getparam('client_user_id');
    $session->set('client_user_id', $clientUserId);

    return $response->withRedirect($this->router->pathFor('channel-index'));
});

$app->map(['GET', 'POST'], '/logout', function (Request $request, Response $response) {
    $this->session->delete('client_user_id');

    return $response->withRedirect($this->router->pathFor('default-index'));
})->setName('index-logout');

$app->get('/channel[/{channel_key}]', function (Request $request, Response $response, $args) use ($container) {
    $session = $container->get('session');
    /** @var \SlimSession\Helper $session */

    $clientUserId = $session->get('client_user_id');
    if (empty($clientUserId)) {
        return $response->withRedirect($this->router->pathFor('default-index'));
    }

    $kollusSettings = $this->settings['kollus'];
    $kollusApiClient = $container->get('kollusApiClient');
    /** @var \Kollus\Component\Client\ApiClient $kollusApiClient */

    $channelKey = isset($args['channel_key']) ? $args['channel_key'] : null;
    $channels = [];
    $existsConfig = !empty($kollusSettings);
    if ($existsConfig) {
        $channels = $kollusApiClient->getChannels();
        $channel = null;

        if (!empty($channels)) {
            if (empty($channelKey)) {
                $channel = $channels[0];
            } else {
                foreach ($channels as $c) {
                    if ($c->getKey() == $channelKey) {
                        $channel = $c;
                        break;
                    }
                }
            }

            if ($channel instanceof \Kollus\Component\Container\Channel) {
                $channelKey = $channel->getKey();
            }
        }
    }

    $result = $kollusApiClient->findChannelMediaContentsByPage($channelKey);

    $data = [
        'channels' => $channels,
        'channelKey' => $channelKey,
        'mediaContents' => $result->items,
        'clientUserId' => $clientUserId,
        'existsConfig' => $existsConfig,
        'kollus' => $kollusSettings,
    ];
    $data['config_not_exist'] = empty($kollusSettings) && is_null($kollusApiClient);
    $data['service_account_key'] = isset($kollusSettings['service_account']['key']) ?
        $kollusSettings['service_account']['key'] : null;
    $data['kollus_domain'] = isset($kollusSettings['domain']) ? $kollusSettings['domain'] : null;

    // Render index view
    return $this->renderer->render($response, 'channel.phtml', $data);
})->setName('channel-index');

$app->post(
    '/auth/play-video-url/{channel_key}/{upload_file_key}',
    function (Request $request, Response $response, $args) use ($container) {
        $session = $container->get('session');
        /** @var \SlimSession\Helper $session */

        $clientUserId = $session->get('client_user_id');
        if (empty($clientUserId)) {
            return $response->withRedirect($this->router->pathFor('default-index'));
        }

        $kollusSettings = $this->settings['kollus'];
        $kollusApiClient = $container->get('kollusApiClient');
        /** @var \Kollus\Component\Client\ApiClient $kollusApiClient */

        $kollusVideoGatewayClient = $container->get('kollusVideoGatewayClient');
        /** @var \Kollus\Component\Client\VideoGatewayClient $kollusVideoGatewayClient */

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

$app->post(
    '/auth/download-video-url/{channel_key}/{upload_file_key}',
    function (Request $request, Response $response, $args) use ($container) {
        $session = $container->get('session');
        /** @var \SlimSession\Helper $session */

        $clientUserId = $session->get('client_user_id');
        if (empty($clientUserId)) {
            return $response->withRedirect($this->router->pathFor('default-index'));
        }

        $kollusSettings = $this->settings['kollus'];
        $kollusApiClient = $container->get('kollusApiClient');
        /** @var \Kollus\Component\Client\ApiClient $kollusApiClient */

        $kollusVideoGatewayClient = $container->get('kollusVideoGatewayClient');
        /** @var \Kollus\Component\Client\VideoGatewayClient $kollusVideoGatewayClient */

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

$app->post(
    '/auth/download-multi-video/{channel_key}',
    function (Request $request, Response $response, $args) use ($container) {
        $session = $container->get('session');
        /** @var \SlimSession\Helper $session */

        $clientUserId = $session->get('client_user_id');
        if (empty($clientUserId)) {
            return $response->withRedirect($this->router->pathFor('default-index'));
        }

        $kollusSettings = $this->settings['kollus'];
        $kollusApiClient = $container->get('kollusApiClient');
        /** @var \Kollus\Component\Client\ApiClient $kollusApiClient */

        $kollusVideoGatewayClient = $container->get('kollusVideoGatewayClient');
        /** @var \Kollus\Component\Client\VideoGatewayClient $kollusVideoGatewayClient */

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

$app->post(
    '/auth/play-video-playlist/{channel_key}',
    function (Request $request, Response $response, $args) use ($container) {
        $session = $container->get('session');
        /** @var \SlimSession\Helper $session */

        $clientUserId = $session->get('client_user_id');
        if (empty($clientUserId)) {
            return $response->withRedirect($this->router->pathFor('default-index'));
        }

        $kollusSettings = $this->settings['kollus'];
        $kollusApiClient = $container->get('kollusApiClient');
        /** @var \Kollus\Component\Client\ApiClient $kollusApiClient */

        $kollusVideoGatewayClient = $container->get('kollusVideoGatewayClient');
        /** @var \Kollus\Component\Client\VideoGatewayClient $kollusVideoGatewayClient */

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

// Run app
$app->run();
