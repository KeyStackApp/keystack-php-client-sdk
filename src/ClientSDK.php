<?php
/**
 * Created by PhpStorm.
 * This file is part of the keystack-php-client-sdk project.
 * Filename: ClientSDK.php
 * Namespace: KeyStackApp
 * User: szilard
 * Date: 03.11.2025
 * Time: 20:52
 */

namespace KeyStackApp;

use KeyStackApp\Adapter\TokenStorageAdapterInterface;
use KeyStackApp\Client\Model\ActivateLicense;
use KeyStackApp\Client\Model\ActivationResponse;
use KeyStackApp\Client\Model\DeactivateLicenseInput;
use KeyStackApp\Client\Model\DeactivationResponse;
use KeyStackApp\Client\Model\LicenseKey;
use KeyStackApp\Client\Model\ValidateLicenseRequest;
use KeyStackApp\Service\ConfiguredApi;

class ClientSDK extends ConfiguredApi
{
    public function __construct(
        ?TokenStorageAdapterInterface $adapter = null,
        ?string $apiKey = null
    ) {
        parent::__construct($adapter, $apiKey);
    }

    /**
     * @throws \Exception
     */
    public function activateLicense(ActivateLicense $activateLicense): ActivationResponse
    {
        return $this->handleApiError(
            fn() => $this->api->activateLicense($activateLicense)
        );
    }

    /**
     * @throws \Exception
     */
    public function validateLicense(ValidateLicenseRequest $validateLicense): LicenseKey
    {
        return $this->handleApiError(
            fn() => $this->api->validateLicense($validateLicense)
        );
    }

    /**
     * @throws \Exception
     */
    public function deactivateLicense(DeactivateLicenseInput $deactivateLicenseInput): DeactivationResponse
    {
        return $this->handleApiError(
            fn() => $this->api->deactivateLicense($deactivateLicenseInput)
        );
    }

    /**
     * @throws \Exception
     */
    public function manifestPublicRead(string $key): object
    {
        return $this->handleApiError(
            fn() => $this->api->manifestPublicRead($key)
        );
    }
}