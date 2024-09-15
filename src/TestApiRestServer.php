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

namespace PHPExperts\PHPTestServer;

class TestApiRestServer
{
    public const KILL_FILE = '/tmp/kill-php-test-server';

    public static $SERVER_PID;

    public static function run(int $port = 45219, ?string $ip = null)
    {
        if (file_exists(self::KILL_FILE)) {
            unlink(self::KILL_FILE);
        }

        // Generate a random port number between 30000 and 64000
        // $port = random_int(30000, 64000);
        if ($ip === null) {
            $ip = '127.0.0.5';
        }

        // Check if the port is being listened to
        $socket = @stream_socket_server("tcp://$ip:$port", $errno, $errstr);
        if (!$socket) {
            exit("Error: $errstr ($errno)\n");
        }

        echo "Server listening on http://$ip:$port\n";

        while (!file_exists(self::KILL_FILE)) {
            $client = @stream_socket_accept($socket);
            if ($client) {
                // Read HTTP request
                $request = fread($client, 1024);
                // dump($request);
                if ($request === '') {
                    return;
                }
                [$headers, $body] = explode("\r\n\r\n", $request, 2);

                $lines = explode("\r\n", $headers);

                $httpHeaders = [];
                foreach ($lines as $i => $line) {
                    if ($i < 2) {
                        continue;
                    }
                    [$key, $value] = explode(': ', $line);

                    $httpHeaders[$key] = $value;
                }
                // dump($httpHeaders);

                $methodLine = explode(' ', $lines[0]);
                $method     = $methodLine[0]; // GET, POST, etc.
                $uri        = $methodLine[1];

                // Process the request method
                switch ($method) {
                    case 'GET':
                        $response = "You made a GET request to $uri";
                        break;
                    case 'POST':
                        $response = "You made a POST request with body: $body";
                        break;
                    case 'PATCH':
                        $response = "You made a PATCH request with body: $body";
                        break;
                    case 'PUT':
                        $response = "You made a PUT request with body: $body";
                        break;
                    case 'DELETE':
                        $response = "You made a DELETE request to $uri";
                        break;
                    default:
                        $response = "Unsupported HTTP method: $method";
                        break;
                }

                $userAgent = $httpHeaders['User-Agent'] ?? 'UNKNOWN';
                $response .= " as $userAgent";

                // Send HTTP response
                $httpResponse = "HTTP/1.1 200 OK\r\n";
                $httpResponse .= "Content-Type: text/plain\r\n";
                $httpResponse .= 'Content-Length: ' . strlen($response) . "\r\n";
                $httpResponse .= "\r\n";
                $httpResponse .= $response;

                fwrite($client, $httpResponse);
                fclose($client);
            }
        }

        unlink(self::KILL_FILE);
    }

    public static function runForked(int $port)
    {
        if (($pid = pcntl_fork()) === -1) {
            exit("Error forking...\n");
        }

        // This is the child process. Run the REST server in here.
        if ($pid === 0) {
            TestApiRestServer::run($port);
            // Kill the child, otherwise it will run the tests in parallel, incorrectly.
            exit;
        } else {
            self::$SERVER_PID = $pid;
            dump("Server PID: $pid");
            usleep(500000);
        }
    }

    public static function shutdown(): void
    {
        if (self::$SERVER_PID === 1 || self::$SERVER_PID === null) {
            return;
        }

        if (posix_kill(TestApiRestServer::$SERVER_PID, SIGKILL) === false) {
            // We should kill the tests right now, due to inability to
            // naturally stop the REST server, so that we don't end up
            // with eternal zombie processes.
            throw new \RuntimeException('Could not successfully stop the Test REST API server.');
        }
    }
}
