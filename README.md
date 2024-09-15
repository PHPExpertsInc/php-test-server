# PHP API Test Server

PHP API Test Server is a PHP Experts, Inc., Project meant to ease the testing of HTTP clients
and REST clients.

It is more or less an echo server. It supports all major HTTP verbs:

GET, POST, PUT, PATCH, and DELETE

## Installation

Via Composer

```bash
composer require --dev phpexperts/php-test-server
```

## Usage

The default port the server listens to is 127.0.0.5:49519.

To run standalone:

    bin/php-test-server [PORT]

To run in a PHP script:

    TestApiRestServer::runForked(45192);
    TestApiRestServer::shutdown();

To run as part of PHPUnit:

    class MyApiTest extends TestApiServerTestCase
    {
    }

## Use cases

Api Rest Server Binary (PHPExperts\PHPTestServer\Tests\ApiRestServerBinary)  
 ✔ Can launch the standalone test server  
 ✔ It is an HTTP server  
 ✔ Can shut down the test server  

Api Rest Server (PHPExperts\PHPTestServer\Tests\ApiRestServer)  
 ✔ Can launch the test server  
 ✔ It is an HTTP server  
 ✔ It supports the GET, POST, PUT, PATCH, and DELETE HTTP verbs  
 ✔ Can shut down the HTTP server  

Api Server Test Case (PHPExperts\PHPTestServer\Tests\ApiServerTestCase)  
 ✔ Accepts GET requests  
 ✔ Accepts POST requests  
 ✔ Accepts PUT requests  
 ✔ Accepts PATCH requests  
 ✔ Accepts DELETE requests  

## Testing

```bash
phpunit --testdox
```

# License

This project is licensed under the [Creative Commons Attribution License v4.0 International](LICENSE.md).

![CC.by License Summary](https://user-images.githubusercontent.com/1125541/93617603-cd6de580-f99b-11ea-9da4-f79c168c97df.png)

# About The Author

[Theodore R. Smith](https://www.phpexperts.pro/]) <theodore@phpexperts.pro>  
GPG Fingerprint: 4BF8 2613 1C34 87AC D28F  2AD8 EB24 A91D D612 5690  
CEO: PHP Experts, Inc.
