<?php

namespace Elenyum\Dashboard\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\Common\EventSubscriber;

use Doctrine\ORM\Events;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsDoctrineListener('postPersist')]
class DatabaseSizeLoggerSubscriber implements EventSubscriber
{
    private EntityManagerInterface $entityManager;
    private LoggerInterface $logger;
    private string $logFilePath;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $elenyumDatabaseStatsLogger,
        #[Autowire('%kernel.logs_dir%')]
        string $logDir
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $elenyumDatabaseStatsLogger;
        $this->logFilePath = $logDir.'/controller/stats.log';
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
        ];
    }

    /**
     * @param LifecycleEventArgs $args
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    public function postPersist(LifecycleEventArgs $args): void
    {
        if ($this->shouldLog()) {
            // Получение размера базы данных
            $databaseSize = $this->getDatabaseSize();

            // Логирование размера базы данных
            $this->logger->info('Database size after insertion', ['database_size_mb' => $databaseSize]);
        }
    }

    /**
     * @return float
     * @throws \Doctrine\DBAL\Exception
     */
    private function getDatabaseSize(): float
    {
        $connection = $this->entityManager->getConnection();
        $platform = $connection->getDatabasePlatform()->getName();

        switch ($platform) {
            case 'mysql':
                $query = "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb 
                          FROM information_schema.TABLES 
                          WHERE table_schema = DATABASE()";
                break;

            case 'postgresql':
                $query = "SELECT pg_database_size(current_database()) / 1024 / 1024 AS size_mb";
                break;

            case 'mssql':
                $query = "SELECT SUM(size) * 8 / 1024 AS size_mb
                          FROM sys.master_files
                          WHERE type = 0 AND database_id = DB_ID()";
                break;

            case 'oracle':
                $query = "SELECT ROUND(SUM(bytes) / 1024 / 1024, 2) AS size_mb
                          FROM user_segments";
                break;

            default:
                throw new \Exception('Unsupported database platform: ' . $platform);
        }

        return (float) $connection->fetchOne($query);
    }

    private function shouldLog(): bool
    {
        if (!file_exists($this->logFilePath)) {
            return true;
        }

        $lastModifiedTime = filemtime($this->logFilePath);
        $lastLogDate = (new \DateTime())->setTimestamp($lastModifiedTime)->format('Y-m-d');
        $currentDate = (new \DateTime())->format('Y-m-d');

        return $lastLogDate !== $currentDate;
    }
}

