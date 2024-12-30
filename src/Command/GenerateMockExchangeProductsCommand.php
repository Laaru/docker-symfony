<?php

namespace App\Command;

use App\Producer\KafkaProducerService;
use Faker\Factory;
use Faker\Generator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mock:products')]
class GenerateMockExchangeProductsCommand extends Command
{
    private Generator $faker;

    public function __construct(
        private readonly KafkaProducerService $producerService,
        private readonly LoggerInterface $logger,
        private readonly string $kafkaTopic
    ) {
        parent::__construct();
        $this->faker = Factory::create();
    }

    protected function configure(): void
    {
        $this->setDescription('Generates mock products in Kafka');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $message = '--- seeding products to Kafka ---';
        $output->writeln([$message]);

        $products = [];
        for ($i = 1; $i <= mt_rand(5, 20); ++$i) {
            $products[] = [
                'id' => mt_rand(1, 999999),
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
        }
        $this->producerService->sendMessage($products, $this->kafkaTopic);


        $message = "--- sent $i products to Kafka ---";
        $output->writeln($message);
        $this->logger->info("mock:products command $message");

        return Command::SUCCESS;
    }
}
