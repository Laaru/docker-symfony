<?php

namespace App\Command;

use Faker\Factory;
use Faker\Generator;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mock:products')]
class GenerateMockExchangeProductsCommand extends Command
{
    private ProducerInterface $producer;
    private Generator $faker;
    private LoggerInterface $logger;

    public function __construct(ProducerInterface $productProducer, LoggerInterface $logger)
    {
        parent::__construct();
        $this->producer = $productProducer;
        $this->faker = Factory::create();
        $this->logger = $logger;
    }

    protected function configure(): void
    {
        $this->setDescription('Generates mock products in RabbitMQ');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $message = '--- seeding products to RabbitMQ ---';
        $output->writeln([$message]);
        $this->logger->info($message);

        for ($i = 1; $i <= 10; ++$i) {
            $product = [
                'id' => $i + 1000000,
                'name' => $this->faker->sentence(mt_rand(1, 2)),
                'measurements' => [
                    'height' => mt_rand(10, 1000),
                    'length' => mt_rand(10, 1000),
                    'width' => mt_rand(10, 1000),
                    'weight' => mt_rand(50, 5000),
                ],
                'description' => $this->faker->paragraph(),
                'cost' => mt_rand(100, 50000),
                'tax' => $this->faker->randomElement([0, 12, 20]),
                'version' => mt_rand(1, 5),
                'color' => mt_rand(1, 10),
                'stores' => $this->faker->randomElements(range(1, 20), mt_rand(1, 5)),
            ];

            $this->producer->publish(json_encode($product));
        }

        $message = "--- sent $i products to Rabbit MQ ---";
        $output->writeln($message);
        $this->logger->info($message);

        return Command::SUCCESS;
    }
}
