---
layout: default
title: Client
nav_order: 3
description: Client
---

The easiest way to create a working `NovaApiClient` object is to add a container (PSR-11)
definiton for `\OrcaServices\NovaApi\Configuration\NovaApiConfiguration::class`.

**Example**

```php
<?php

use OrcaServices\NovaApi\Configuration\NovaApiConfiguration;
use Psr\Container\ContainerInterface;
// ...

return [
    // ...

    NovaApiConfiguration::class => function (ContainerInterface $container) {
        $settings = (array)$container->get('settings')['nova'];

        return new NovaApiConfiguration($settings);
    },
];
```

Then use dependency injection to get the `NovaApiClient` object.

```php
<?php

use OrcaServices\NovaApi\Client\NovaApiClient;
// ...

final class NovaSwissPassTicketGenerator
{
    /**
     * @var NovaApiClient
     */
    private $novaApiClient;

    /**
     * The constructor.
     *
     * @param NovaApiClient $novaApiClient The nova API client
     */
    public function __construct(NovaApiClient $novaApiClient)
    {
        $this->novaApiClient = $novaApiClient;
    }

    // ...

}
```
