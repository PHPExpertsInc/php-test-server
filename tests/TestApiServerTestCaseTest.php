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

use PHPExperts\PHPTestServer\TestApiServerTestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class TestApiServerTestCaseTest extends TestApiServerTestCase
{
    private const BASE_URI = 'http://127.0.0.5:45219';

    /** @testdox Accepts GET requests */
    public function testCanMakeGetRequests(HttpClientInterface $httpClient = null)
    {
        if ($httpClient === null) {
            $httpClient = HttpClient::create([
                'base_uri' => self::BASE_URI,
            ]);
        }

        try {
            $response = $httpClient->request('GET', '/asdf');
            self::assertEquals(200, $response->getStatusCode());
            self::assertStringStartsWith('You made a GET request to /asdf as Symfony HttpClient', $response->getContent());
        } catch (\Throwable $e) {
            // Handle exceptions if needed
            self::fail('HTTP requests failed: ' . $e->getMessage());
        }
    }


    /** @testdox Accepts POST requests */
    public function testCanMakePostRequests(HttpClientInterface $httpClient = null)
    {
        if ($httpClient === null) {
            $httpClient = HttpClient::create([
                'base_uri' => self::BASE_URI,
            ]);
        }

        $testNormalFormPost = function () use ($httpClient) {
            $response = $httpClient->request('POST', '/poster', [
                'body' => [
                    'param1' => 'value1',
                    'param2' => 'value2',
                ]
            ]);

            self::assertEquals(200, $response->getStatusCode());
            self::assertStringStartsWith('You made a POST request with body: param1=value1&param2=value2 as Symfony HttpClient', $response->getContent());
        };
        $testNormalFormPost();

        $testJsonPost = function () use ($httpClient) {
            $response = $httpClient->request('POST', '/poster', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'param1' => 'value1',
                    'param2' => 'value2',
                ]
            ]);

            self::assertEquals(200, $response->getStatusCode());
            self::assertStringStartsWith('You made a POST request with body: {"param1":"value1","param2":"value2"} as Symfony HttpClient', $response->getContent());
        };
        $testJsonPost();
    }

    /** @testdox Accepts PUT requests */
    public function testCanMakePutRequests(HttpClientInterface $httpClient = null)
    {
        if ($httpClient === null) {
            $httpClient = HttpClient::create([
                'base_uri' => self::BASE_URI,
            ]);
        }

        $response = $httpClient->request('PUT', '/put', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'param1' => 'value1',
                'param2' => 'value2',
            ])
        ]);

        self::assertEquals(200, $response->getStatusCode());
        self::assertStringStartsWith('You made a PUT request with body: {"param1":"value1","param2":"value2"} as Symfony HttpClient', $response->getContent());
    }

    /** @testdox Accepts PATCH requests */
    public function testCanMakePatchRequests(HttpClientInterface $httpClient = null)
    {
        if ($httpClient === null) {
            $httpClient = HttpClient::create([
                'base_uri' => self::BASE_URI,
            ]);
        }

        $response = $httpClient->request('PATCH', '/patch', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'param1' => 'value1',
                'param2' => 'value2',
            ])
        ]);

        self::assertEquals(200, $response->getStatusCode());
        self::assertStringStartsWith('You made a PATCH request with body: {"param1":"value1","param2":"value2"} as Symfony HttpClient', $response->getContent());
    }

    /** @testdox Accepts DELETE requests */
    public function testCanMakeDeleteRequests(HttpClientInterface $httpClient = null)
    {
        if ($httpClient === null) {
            $httpClient = HttpClient::create([
                'base_uri' => self::BASE_URI,
            ]);
        }

        $response = $httpClient->request('DELETE', '/object/555');

        self::assertEquals(200, $response->getStatusCode());
        self::assertStringStartsWith('You made a DELETE request to /object/555 as Symfony HttpClient', $response->getContent());
    }
}
