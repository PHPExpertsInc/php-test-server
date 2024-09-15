<?php declare(strict_types=1);

/**
 * This file is part of PHP API Test Server, a PHP Experts, Inc., Project.
 *
 * Copyright © 2024 PHP Experts, Inc.
 * Author: Theodore R. Smith <theodore@phpexperts.pro>
 *   GPG Fingerprint: 4BF8 2613 1C34 87AC D28F  2AD8 EB24 A91D D612 5690
 *   https://www.phpexperts.pro/
 *   https://github.com/PHPExpertsInc/php-test-server
 *
 * This file is licensed under the MIT License.
 */

namespace PHPExperts\PHPTestServer\Tests;

use AssertionError;
use PHPExperts\PHPTestServer\TestApiRestServer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\ResponseInterface;

class TestApiRestServerTest extends TestCase
{
    public static function launchTestServer()
    {
        TestApiRestServer::runForked(49519);
        sleep(2);
    }

    public static function killTestServer()
    {
        touch(TestApiRestServer::KILL_FILE);
        usleep(100);

        TestApiRestServer::shutdown();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        self::killTestServer();
    }

    public static function assertConnection($ip, $port, $timeout = 3): void
    {
        // Construct the address string
        $address = "tcp://$ip:$port";

        // Initialize error variables
        $errno = 0;
        $errstr = '';

        // Attempt to create a client socket
        $socket = @stream_socket_client(
            $address,
            $errno,
            $errstr,
            $timeout
        );

        // Check if the socket was successfully created
        if ($socket) {
            fclose($socket);

            self::assertTrue(true);
            return;
        }

        throw new AssertionError("Failed to connect to $ip on port $port: $errstr ($errno)");
    }

    public function assertHttpConnection(string $url): void
    {
        $client = HttpClient::create([
            'timeout' => 5,
        ]);

        $response = $client->request('GET', $url);
        self::assertInstanceOf(ResponseInterface::class, $response);
        self::assertEquals(200, $response->getStatusCode());
        self::assertStringStartsWith('You made a GET request to / as Symfony HttpClient', $response->getContent());
    }

    public function testCanLaunchTheTestServer()
    {
        self::launchTestServer();
        // Try to connect to the server.
        self::assertConnection('127.0.0.5', 49519);
    }

    /** @testdox It is an HTTP server */
    public function testItIsAnHttpServer()
    {
        self::launchTestServer();
        sleep(1);
        self::assertHttpConnection('http://127.0.0.5:49519/');
    }

    /** @testdox It supports the GET, POST, PUT, PATCH, and DELETE HTTP verbs */
    public function testItAcceptsTheGetPostPutPatchDeleteHttpVerbs()
    {
        self::launchTestServer();
        sleep(1);
        $client = HttpClient::create([
            'base_uri' => 'http://127.0.0.5:49519',
            'timeout'  => 5,
        ]);

        foreach (['POST', 'PUT', 'PATCH'] as $httpVerb) {
            $response = $client->request($httpVerb, '/asdf', [
                'json' => ['foo' => 'bar']
            ]);
            self::assertInstanceOf(ResponseInterface::class, $response);
            self::assertEquals(200, $response->getStatusCode());
            self::assertStringStartsWith("You made a $httpVerb request with body: {\"foo\":\"bar\"} as Symfony HttpClient", $response->getContent());
        }

        $response = $client->request('DELETE', '/object/111');
        self::assertInstanceOf(ResponseInterface::class, $response);
        self::assertEquals(200, $response->getStatusCode());
        self::assertStringStartsWith("You made a DELETE request to /object/111 as Symfony HttpClient", $response->getContent());
    }

    /** @testdox Can shut down the HTTP server */
    public function testCanShutDownHttpServer()
    {
        self::launchTestServer();
        self::killTestServer();
        sleep(1);

        try {
            self::assertConnection('127.0.0.5', '49519');
        } catch (AssertionError $e) {
            self::assertEquals('Failed to connect to 127.0.0.5 on port 49519: Connection refused (111)', $e->getMessage());
        }
    }
}
