# HTTP Client

A simple and flexible PHP HTTP client for sending HTTP/HTTPS requests, including support for various request methods and logging.

## Features

- **Support for multiple HTTP methods:** GET, POST, PUT, DELETE, PATCH, HEAD, OPTIONS.
- **Logging capabilities:** Built-in logging for requests and responses with customizable log levels.
- **Cookie management:** Option to handle cookies with a specified file.
- **Customizable options:** Easily configure request options and headers.

## Installation

To install the `ariyx/http-client` package, you can use Composer:

```bash
composer require ariyx/http-client
```

## Usage

Here's a quick guide on how to use the `Ariyx\HttpClient` class in your PHP project:

### Basic Usage

```php
<?php

require 'vendor/autoload.php';

use Ariyx\HttpClient\HttpClient;
use Ariyx\HttpClient\Logger;

// Create a new Logger instance
$logger = new Logger('http-client.log');

// Create a new HttpClient instance
$client = new HttpClient(
    'https://api.example.com',
    [],
    [],
    null,
    60,
    $logger
);

// Send a GET request
$response = $client->get(['param1' => 'value1']);

// Send a POST request
$response = $client->post(['data1' => 'value1']);

// Send a PUT request
$response = $client->put(['data1' => 'value1']);

// Send a DELETE request
$response = $client->delete();

// Send a PATCH request
$response = $client->patch(['data1' => 'value1']);
```

### Configuration

- **Headers:** Add headers using the `addHeader` method.
- **Options:** Customize request options using the `addOption` method.
- **Cookies:** Set a cookie file using the `setCookieFile` method.

### Logging

By default, the HTTP client logs requests and responses to `http-client.log`. You can customize the log file name or use different log levels (INFO, WARNING, ERROR).

## Contributing

If you would like to contribute to this project, please fork the repository and submit a pull request with your changes.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Contact

For any questions or issues, you can contact the author:

- **Name:** Armin Malekzadeh
- **Email:** arminmalekzwde@gmail.com
