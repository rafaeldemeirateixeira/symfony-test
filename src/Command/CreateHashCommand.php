<?php

namespace App\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CreateHashCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'avato:test';

    /** @var string */
    protected static $defaultDescription = 'Registra novas hashes.';

    /** @var HttpClientInterface */
    private HttpClientInterface $client;

    /** @var ContainerBagInterface */
    private ContainerBagInterface $params;

    /**
     * @param HttpClientInterface $client
     * @param ContainerBagInterface $params
     */
    public function __construct(
        HttpClientInterface $client,
        ContainerBagInterface $params
    ) {
        parent::__construct();
        $this->client = $client;
        $this->params = $params;
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
                
                usleep($this->params->get('hashes.create_sleep_time') ?? 6250000);
            }

            return Command::SUCCESS;
        } catch (Exception $e) {
            return Command::FAILURE;
        }
    }
}
