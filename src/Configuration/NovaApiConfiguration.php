<?php

namespace OrcaServices\NovaApi\Configuration;

/**
 * Configuration.
 */
final class NovaApiConfiguration
{
    /**
     * @var array
     */
    private $settings;

    /**
     * The constructor.
     *
     * @param array $settings The settings
     */
    public function __construct(array $settings = [])
    {
        $this->settings = $settings;
    }

    /**
     * @return string The version
     */
    public function getNovaApiVersion(): string
    {
        return $this->settings['version'] ?? 'v14';
    }

    /**
     * Get default http settings.
     *
     * @return array The settings
     */
    public function getDefaultHttpSettings(): array
    {
        return (array)$this->settings['default'];
    }

    /**
     * Get NOVA OAuth 2 (Single sign-on) settings.
     *
     * @return array The settings
     */
    public function getWebServiceSsoClientSettings(): array
    {
        return array_replace_recursive($this->getDefaultHttpSettings(), $this->settings['sso']);
    }

    /**
     * Get NOVA SOAP-Webservice endpoint options.
     *
     * @return array The settings
     */
    public function getWebServiceClientSettings(): array
    {
        return array_replace_recursive($this->getDefaultHttpSettings(), $this->settings['webservice']);
    }
}
