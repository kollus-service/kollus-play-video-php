# Alternative Ways

## If you can't use jwt library by composer

[Composer](https://getcomposer.org/doc/00-intro.md) requires PHP 5.3.2+ to run. 

(PHP 5 >= 5.3.2)

Alternative functions
```php
/**
 * base64_urlencode
 *
 * @param array $str
 * @return string
 */
function base64_urlencode($str) {
    return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
}

/**
 * jwt_encode
 *
 * @param array $payload
 * @param string $key
 * @return string
 */
function jwt_encode($payload, $key) {
    $jwtHead = base64_urlencode(json_encode(array('typ' => 'JWT', 'alg' => 'HS256')));
    $jsonPayload = base64_urlencode(json_encode($payload));
    $signature = base64_urlencode(hash_hmac('SHA256', $jwtHead . '.' . $jsonPayload, $key, true));
    
    return $jwtHead . '.' . $jsonPayload . '.' . $signature;
}

```

## if you can't use json library

json_encode doc is [here](http://php.net/manual/en/function.json-encode.php).

(PHP 5 >= 5.2.0, PECL json >= 1.2.0, PHP 7)

install [json](http://pecl.php.net/package/json) Package Using [PECL](http://php.net/manual/kr/install.pecl.php)

or use JSON.php in [Services_JSON](http://pear.php.net/pepr/pepr-proposal-show.php?id=198)

## if you can't use hash_hmac

hash_hmac doc is [here](http://php.net/manual/en/function.hash-hmac.php).

(PHP 5 >= 5.1.2, PHP 7, PECL hash >= 1.1)

install [Hash](http://pecl.php.net/package/hash) Package Using [PECL](http://php.net/manual/kr/install.pecl.php)

## Jwt webtoken Sample Codes

```php
...

$securityKey = 'security_key';
$customKey = 'custom_key';

$clientUserId = 'client_user_id';
$expireTime = 3600; // 1 hour
$mediaContentKey = 'media_content_key';
$mediaProfileKey = 'media_profile_key'; // optional

$payload = array(
    'cuid' => $clientUserId,
    'expt' => $expireTime,
    'mc' => array(
        array(
            'mckey' => $mediaContentKey,
            'mcpf' => $mediaProfileKey
        )
    )
);

$jwtToken = jwt_encode($payload, $securityKey);

$webTokenURL = 'http://v.kr.kollus.com/s?jwt=' . $jwtToken . '&custom_key=' . $customKey;

...

?>

...

<iframe src="<?php echo $webTokenURL; ?>" allowfullscreen></iframe>

...
```
