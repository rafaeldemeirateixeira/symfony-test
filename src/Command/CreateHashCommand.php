<?php

namespace App\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CreateHashCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'avato:test';

    /** @var string */
    protected static $defaultDescription = 'Registra novas hashes.';

    /** @var HttpClientInterface */
    private HttpClientInterface $client;

    /**
     * @param HttpClientInterface $client
     */
    public function __construct(HttpClientInterface $client)
    {
        parent::__construct();
        $this->client = $client;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->addArgument('input', InputArgument::REQUIRED, 'Texto de entrada para gerar hash.');
        $this->addOption('requests', null, InputArgument::OPTIONAL, 'Total de requisições.', 1);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $totalRequest = $input->getOption('requests');
        $inputValue = $input->getArgument('input');
        
        try {
            for ($i = 0; $i < $totalRequest; $i++) { 
                $response = $this->client->request('POST', 'http://brasiltecpar_nginx/api/hashes', [
                    'json' => [
                        'input' => $inputValue,
                        'block_number' => bcadd($i, 1),
                    ]
                ]);
                $decodedPayload = $response->toArray();
                $inputValue = $decodedPayload['hash'];
                
                if ($totalRequest > 10) {
                    usleep(6250000);
                } else {
                    usleep(1000000);
                }
            }

            return Command::SUCCESS;
        } catch (Exception $e) {
            return Command::FAILURE;
        }
    }
}
