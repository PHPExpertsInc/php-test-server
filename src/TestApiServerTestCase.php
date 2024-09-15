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

use PHPUnit\Framework\TestCase;

class TestApiServerTestCase extends TestCase
{
    private static int $SERVER_PID;

    public static function setUpBeforeClass(): void
    {
        if (($pid = pcntl_fork()) === -1) {
            exit("Error forking...\n");
        }

        // This is the child process. Run the REST server in here.
        if ($pid === 0) {
            TestApiRestServer::run();
        } else {
            self::$SERVER_PID = $pid;

            // Wait 1/2 a second so that the server has time to boot.
            usleep(500);
        }
    }

    public static function tearDownAfterClass(): void
    {
        $PID = self::$SERVER_PID;
        dump("SHUTTING DOWN TEST SERVER with PID {$PID}");
        // It's now time to kill the test REST API server.
        if (posix_kill(self::$SERVER_PID, SIGKILL) === false) {
            // We should kill the tests right now, due to inability to
            // naturally stop the REST server, so that we don't end up
            // with eternal zombie processes.
            throw new \RuntimeException('Could not successfully stop the Test REST API server.');
        }

        // Give the server time to die before stomping all over it with new tests.
        sleep(1);
    }

    protected function grabPrivateProperty(object $object, string $property)
    {
        $mirror = new \ReflectionObject($object);
        $p      = $mirror->getProperty($property);
        $p->setAccessible(true);

        return $p->getValue($object);
    }
}
