---
layout: default
title: Configuration
nav_order: 2
description: Configuration
---

The `NovaHttpClientFactory` class requires a `NovaApiConfiguration` object with with all settings.

Create an object by instantiating the `OrcaServices\NovaApi\Configuration\NovaApiConfiguration` 
class. The most common use case, while not the most explicit, is to pass an array 
of configuration to it:

```php
use OrcaServices\NovaApi\Configuration\NovaApiConfiguration;

$configuration = new NovaApiConfiguration($configArray);
```

Here is a table for the key-value pairs that should be in configuration array.

Parameter | Values | Default | Description
--- | --- | --- | ---
version | string | v14 | NOVA API version.
default | array | [] | Default HTTP settings for all requests (sso and webservice).
sso | array | [] | Single Sign On (SSO) endpoint and credentials.
webservice | array | [] | The SOAP webservice endpoint (without credentials).

## Default request settings

Key: **default**

The documentation for all request parameters can be found in 
the [Guzzle documentation](http://docs.guzzlephp.org/en/stable/request-options.html).

### Example

```php
'default' => [
    'debug' => false,
    'base_uri' => null,
    // Should be disabled
    'cookies' => false,
    // Accept all SSL certificates (important),
    // because NOVA regularly changes its SSL root certificates,
    // and we don't know when that will happen.
    'verify' => false,
    'headers' => [
        'Content-Type' => 'text/xml;charset=UTF-8',
        'User-Agent' => 'NovaApiClient/1.0',
    ],
    'timeout' => 30,
    'connect_timeout' => 30,
],
```

## Mocking

You can create a mocked response queue by defining a custom guzzle handler:

```php
<?php

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
// ...

$settings = [
    'default' => [
        // ...
        'handler' => HandlerStack::create(new MockHandler($responses)),
    ],
];

```

## Single Sign On Settings

Key: **sso**

Example:

```php
'sso' => [
    'base_uri' => 'https://sso.example.com',
    'client_id' => 'username',
    'client_secret' => 'password',
],
```

## Webservice Settings

Key: **webservice**

Don't provide any credentials here, because the single sign on process will set the token for you.
 
**Example:**

```php
'webservice' => [
    'base_uri' => 'https://nova-int.api.example.com',
],
```

## Example configuration

```php
$settings = [
    // NOVA API version
    'version' => 'v14',
    // Default HTTP settings for SSO and the webservice
    'default' => [
        'debug' => false,
        // Should be disabled
        'cookies' => false,
        // Accept all SSL certificates (important),
        // because NOVA regularly changes its SSL root certificates,
        // and we don't know when that will happen.
        'verify' => false,
        'headers' => [
            'Content-Type' => 'text/xml;charset=UTF-8',
            'User-Agent' => 'NovaApiClient/1.0',
        ],
        'timeout' => 30,
        'connect_timeout' => 30,
    ],
    // Single Sign On
    'sso' => [
        'base_uri' => 'https://sso.example.com',
        'client_id' => 'username',
        'client_secret' => 'password',
    ],
    // The NOVA SOAP endpoint
    'webservice' => [
        'base_uri' => 'https://nova-int.api.example.com',
    ],
];
```
