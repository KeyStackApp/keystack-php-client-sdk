<?php
/**
 * Created by PhpStorm.
 * This file is part of the keystack-php-client-sdk project.
 * Filename: ConfiguredApi.php
 * Namespace: KeyStackApp\Service
 * User: szilard
 * Date: 03.11.2025
 * Time: 22:00
 */

namespace KeyStackApp\Service;

use KeyStackApp\Adapter\SessionAdapter;
use KeyStackApp\Adapter\TokenStorageAdapterInterface;
use KeyStackApp\Client\Api\ClientApi;
use KeyStackApp\LoginManager;

class ConfiguredApi
{
    protected const API_URL_TEMPLATE = 'https://{project}.api.keystack.app';
    protected LoginManager $loginManager;
    protected ClientApi $api;

    public function __construct(
        protected ?TokenStorageAdapterInterface $adapter,
        protected ?string $apiKey
    ) {
        $this->api = new ClientApi();
        $this->apiKey = $apiKey ?? getenv('KEYSTACK_API_KEY');
        $this->adapter = $adapter ?? new SessionAdapter();
        $this->loginManager = new LoginManager();
    }

    public function configureApi(): void
    {
        if (!$this->adapter->getToken()) {
            $this->loginManager->login($this->adapter, $this->apiKey);
        }
        $this->api->getConfig()->setHost($this->loginManager->getApiUrl($this->apiKey, self::API_URL_TEMPLATE));
        $this->api->getConfig()->setApiKeyPrefix('bearer', 'Bearer');
        $this->api->getConfig()->setAccessToken($this->adapter->getToken());
    }

    /**
     * @throws \Exception
     */
    public function handleApiError(\Closure $callback): mixed
    {
        try {
            $this->configureApi();

            return $callback();
        } catch (\Exception $e) {
            if ($e->getCode() == 401) {
                $this->loginManager->login($this->adapter, $this->apiKey);

                $this->api->getConfig()->setAccessToken($this->adapter->getToken());

                return $callback();
            }

            throw $e;
        }
    }
}