<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Marketplace\TestApps\Command;

use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppCommand;
use Akeneo\Connectivity\Connection\Application\Apps\Command\DeleteAppHandler;
use Akeneo\Connectivity\Connection\Domain\Marketplace\TestApps\Persistence\DeleteTestAppQueryInterface;
use Akeneo\Connectivity\Connection\Domain\Marketplace\TestApps\Persistence\GetTestAppQueryInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteTestAppHandler
{
    public function __construct(
        private GetTestAppQueryInterface $getTestAppQuery,
        private DeleteTestAppQueryInterface $deleteTestAppQuery,
        private DeleteAppHandler $deleteAppHandler,
    ) {
    }

    public function handle(DeleteTestAppCommand $testAppCommand): void
    {
        $testAppId = $testAppCommand->getTestAppId();

        $testAppData = $this->getTestAppQuery->execute($testAppId);
        if (null === $testAppData) {
            throw new \InvalidArgumentException(\sprintf('Test app with %s client_id not found.', $testAppId));
        }

        $this->deleteTestAppQuery->execute($testAppId);

        if ($testAppData['connected'] ?? false) {
            $this->deleteAppHandler->handle(new DeleteAppCommand($testAppId));
        }
    }
}
