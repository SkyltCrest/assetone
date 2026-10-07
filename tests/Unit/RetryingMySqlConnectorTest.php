<?php

namespace Tests\Unit;

use App\Support\RetryingMySqlConnector;
use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;

class RetryingMySqlConnectorTest extends TestCase
{
    /** A connector whose first $failures attempts fail with the given message. */
    private function connector(int $failures, string $message): RetryingMySqlConnector
    {
        return new class($failures, $message) extends RetryingMySqlConnector
        {
            public int $attempts = 0;

            protected array $retryDelays = [1, 1, 1];

            public function __construct(private int $failures, private string $message)
            {
            }

            protected function createPdoConnection($dsn, $username, #[\SensitiveParameter] $password, $options)
            {
                if (++$this->attempts <= $this->failures) {
                    throw new PDOException($this->message);
                }

                return new PDO('sqlite::memory:');
            }
        };
    }

    public function test_it_keeps_trying_while_the_server_refuses_connections(): void
    {
        $connector = $this->connector(3, 'SQLSTATE[HY000] [2002] Connection refused');

        $this->assertInstanceOf(PDO::class, $connector->createConnection('dsn', [], []));
        $this->assertSame(4, $connector->attempts);
    }

    public function test_it_gives_up_after_the_last_attempt(): void
    {
        $connector = $this->connector(9, 'SQLSTATE[HY000] [2002] Connection refused');

        try {
            $connector->createConnection('dsn', [], []);
            $this->fail('The connection should not have succeeded.');
        } catch (PDOException) {
            $this->assertSame(4, $connector->attempts);
        }
    }

    public function test_it_does_not_retry_errors_that_waiting_cannot_fix(): void
    {
        $connector = $this->connector(9, "SQLSTATE[HY000] [1045] Access denied for user 'x'");

        try {
            $connector->createConnection('dsn', [], []);
            $this->fail('The connection should not have succeeded.');
        } catch (PDOException) {
            $this->assertSame(1, $connector->attempts);
        }
    }
}
