<?php

namespace App\Support;

use Illuminate\Database\Connectors\MySqlConnector;
use Throwable;

/**
 * Opens MySQL connections, waiting briefly and trying again when the server
 * cannot be reached. The hosted database now and then refuses new connections
 * for a moment; Laravel's own single, immediate retry lands inside the same
 * moment, and the request then failed with a 500.
 */
class RetryingMySqlConnector extends MySqlConnector
{
    /**
     * How long to wait before each further attempt, in milliseconds.
     *
     * @var list<int>
     */
    protected array $retryDelays = [150, 400, 1000];

    protected function tryAgainIfCausedByLostConnection(Throwable $e, $dsn, $username, #[\SensitiveParameter] $password, $options)
    {
        foreach ($this->retryDelays as $delay) {
            if (! $this->causedByLostConnection($e)) {
                break;
            }

            usleep($delay * 1000);

            try {
                return $this->createPdoConnection($dsn, $username, $password, $options);
            } catch (Throwable $next) {
                $e = $next;
            }
        }

        throw $e;
    }
}
