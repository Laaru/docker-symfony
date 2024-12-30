<?php

namespace App\Command;

use App\Consumer\KafkaProductConsumer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'kafka:consume:products',
    description: 'Consume messages from Kafka topic'
)]
class KafkaProductConsumerCommand extends Command
{
    public function __construct(
        private readonly KafkaProductConsumer $kafkaConsumerService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Consume messages from Kafka topic');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Kafka consumer started...');

        $this->kafkaConsumerService->consume();

        $output->writeln('Kafka consumer stopped');

        return Command::FAILURE;
    }
}
