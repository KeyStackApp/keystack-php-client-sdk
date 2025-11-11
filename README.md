# KeyStack PHP Client SDK

Elegant PHP SDK for the KeyStack API. This tiny helper wraps the generated API client and the auth package to give you a simple, batteries‑included way to:

- Authenticate with a single API key (Bearer JWT handled for you)
- Activate and validate license keys
- Deactivate licenses
- Read public manifests

It is built on top of:
- keystackapp/keystack-php-client (HTTP client)
- keystackapp/keystack-php-auth (secure API‑key → JWT login, token storage adapters)

## Requirements
- PHP 8.1+
- Composer

## Installation
```bash
composer require keystackapp/keystack-php-client-sdk
```

## Quick start
The SDK can discover your API key from the KEYSTACK_API_KEY environment variable. You can also inject your own token storage adapter.

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use KeyStackApp\ClientSDK;
use KeyStackApp\Adapter\SessionAdapter; // from keystack-php-auth

// Option A: via environment variable
// putenv('KEYSTACK_API_KEY=ks_live_xxx');

$sdk = new ClientSDK(
    adapter: new SessionAdapter(), // or omit to default to PHP session
    apiKey: getenv('KEYSTACK_API_KEY') // or pass your API key string directly
);

// Now call SDK methods (examples below)
```

Tip: You can also configure the base API URL (useful for staging) via an env var or constant:
- Env: FIREBOOST_API_URL=https://{project}.api.keystack.app
- Constant: define('FIREBOOST_API_URL', 'https://{project}.api.keystack.app');
The {project} placeholder is automatically filled from your API key payload.

## Usage
Below are minimal, copy‑pasteable snippets for each available method.

### Activate a license
```php
use KeyStackApp\Client\Model\ActivateLicense;
use KeyStackApp\Client\Model\ActivateLicenseFingerprint;

$body = new ActivateLicense([
    'licenseKey' => 'LIC-XXXX-XXXX',
    'fingerprint' => new ActivateLicenseFingerprint([
        'siteUrl' => 'https://example.com',
        'pluginVersion' => '1.2.3',
        'phpVersion' => PHP_VERSION,
    ]),
]);

$response = $sdk->activateLicense($body);
// $response is \KeyStackApp\Client\Model\ActivationResponse
```

### Validate a license
```php
use KeyStackApp\Client\Model\ValidateLicenseRequest;

$body = new ValidateLicenseRequest([
    // Populate request fields as needed by your API configuration
    // e.g. 'licenseKey' => 'LIC-XXXX-XXXX', 'siteUrl' => 'https://example.com'
]);

$license = $sdk->validateLicense($body);
// $license is \KeyStackApp\Client\Model\LicenseKey
```

### Deactivate a license
```php
use KeyStackApp\Client\Model\DeactivateLicenseInput;

$body = new DeactivateLicenseInput([
    // e.g. 'licenseKey' => 'LIC-XXXX-XXXX', 'reason' => 'user_request'
]);

$result = $sdk->deactivateLicense($body);
// $result is \KeyStackApp\Client\Model\DeactivationResponse
```

### Read a public manifest
```php
$manifest = $sdk->manifestPublicRead('my-public-manifest-key');
// $manifest is a generic object decoded from JSON
```

## Token storage adapters
The auth package provides multiple adapters to persist the short‑lived JWT and a login‑attempt counter. Choose what fits your app:

- SessionAdapter (default): stores token in PHP native session
- FileAdapter: stores token on disk
- RedisAdapter: stores token in Redis
- DatabaseAdapter: simple PDO‑based table storage

Example with a file adapter:
```php
use KeyStackApp\Adapter\FileAdapter;

$adapter = new FileAdapter(__DIR__ . '/.keystack_token');
$sdk = new ClientSDK($adapter, 'ks_live_xxx');
```

You can also implement your own adapter by implementing KeyStackApp\Adapter\TokenStorageAdapterInterface.

## Error handling
All SDK calls automatically attempt to re‑authenticate once if the server returns HTTP 401 (expired token) and then retry the original request. For other errors, the underlying exception will be thrown. Wrap calls in try/catch to handle API errors gracefully.

```php
try {
    $license = $sdk->validateLicense($body);
} catch (\Throwable $e) {
    // Log, map to your domain errors, show friendly messages, etc.
}
```

## Advanced configuration
Internally, the SDK configures the generated ClientApi with:
- Host: derived from your API key via LoginManager → getApiUrl()
- Authorization: Bearer <JWT>, automatically managed

If you need direct, low‑level access, you can use the client in vendor/keystackapp/keystack-php-client/ (see its README and docs folder).
