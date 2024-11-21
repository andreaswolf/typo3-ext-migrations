<?php

declare(strict_types=1);

namespace KayStrobach\Migrations\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Event\MigrationsVersionEventArgs;
use Doctrine\Migrations\Events;
use Psr\Log\LoggerInterface;

final class PrintMigrationVersionListener implements EventSubscriber
{
    private ?LoggerInterface $logger = null;

    public function __construct(private DependencyFactory $dependencyFactory)
    {
    }

    public function onMigrationsVersionExecuting(MigrationsVersionEventArgs $args): void
    {
        $this->getLogger()->notice(sprintf('Executing %s', $args->getPlan()->getVersion()));
    }

    /**
     * @return list<Events::*>
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::onMigrationsVersionExecuting,
        ];
    }

    private function getLogger(): LoggerInterface
    {
        if ($this->logger === null) {
            $this->logger = $this->dependencyFactory->getLogger();
        }
        return $this->logger;
    }
}
