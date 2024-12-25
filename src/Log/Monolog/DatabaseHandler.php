<?php

namespace App\Log\Monolog;

use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

class DatabaseHandler extends AbstractProcessingHandler
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function write(array|LogRecord $record): void
    {
        $connection = $this->entityManager->getConnection();

        $schemaManager = $connection->createSchemaManager();
        $tables = $schemaManager->listTables();

        $tableExists = false;
        foreach ($tables as $table) {
            if ('log' === $table->getName()) {
                $tableExists = true;
                break;
            }
        }

        if (!$tableExists) {
            return;
        }

        $log = new Log();
        $log->setMessage($record['message']);
        $log->setContext($record['context']);
        $log->setLevel($record['level']);
        $log->setLevelName($record['level_name']);
        $log->setChannel($record['channel']);
        $log->setExtra($record['extra']);
        $log->setFormatted($record['formatted']);
        $log->setDatetime($record['datetime']);

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }
}
