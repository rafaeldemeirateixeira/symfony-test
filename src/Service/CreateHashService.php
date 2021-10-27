<?php

namespace App\Service;

use App\Entity\Hashes;
use App\Repository\HashesRepository;
use DateInterval;
use DateTime;

use function Symfony\Component\DependencyInjection\Loader\Configurator\env;
use function Symfony\Component\String\u;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class CreateHashService
{
    /** @var HashesRepository */
    private HashesRepository $hashesRepository;

    /** @var ContainerBagInterface */
    private ContainerBagInterface $params;

    /**
     * @param HashesRepository $hashesRepository
     */
    public function __construct(
        HashesRepository $hashesRepository,
        ContainerBagInterface $params
    ) {
        $this->hashesRepository = $hashesRepository;
        $this->params = $params;
    }

    /**
     * @param array $data
     * @param string $ip
     * @return Hashes
     */
    public function createHash(array $data, string $ip): Hashes
    {
        $now = new DateTime();
        $subDate = $now->sub(new DateInterval('PT1M'))->format('Y-m-d H:i:s');
        $totalLastMinute = $this->hashesRepository->getLastMinuteByIp($subDate, $ip);

        if ($totalLastMinute >= 10) {
            throw new TooManyRequestsHttpException(60, 'Too Many Attempts.');
        }

        $candidateKeys = $this->candidateKeys($this->params->get('hashes.total_candidate_keys'));
        $selectedKey = array_rand($candidateKeys, 1);
        $key = $candidateKeys[$selectedKey];
        $generatedmMd5 = md5(u($data['input'])->append($key));
        $attempts = $this->searchKey($data['input'], $key, $generatedmMd5, $candidateKeys);
        
        $hash = [
            'input' => $data['input'],
            'hash' => u('0000')->append($generatedmMd5),
            'key' => $key,
            'block_number' => $data['block_number'],
            'attempts' => $attempts,
            'ip' => $ip
        ];

        return $this->hashesRepository->saveHash($hash);
    }

    /**
     * @param mixed $input
     * @param mixed $strength
     * @return string
     */
    public function keyGenerate(int $strength = 8)
    {
        $input = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $inputLength = strlen($input);
        $randomString = '';
        for ($i = 0; $i < $strength; $i++) {
            $randomCharacter = $input[mt_rand(0, $inputLength - 1)];
            $randomString .= $randomCharacter;
        }

        return $randomString;
    }

    /**
     * @param int $total
     * @return array
     */
    public function candidateKeys(int $total)
    {
        $canditades = 0;
        $array = [];
        while ($canditades <= $total) {
            $array[] = $this->keyGenerate();
            $canditades++;
        }

        return $array;
    }

    /**
     * @param string $input
     * @param string $key
     * @param string $hash
     * @return int
     */
    public function searchKey(string $input, string $key, string $hash, array $candidateKeys): int
    {
        $attempts = 0;
        foreach ($candidateKeys as $candidateKey) {
            if (md5(u($input)->append($candidateKey)) == $hash) {
                break;
            }
            $attempts++;
        }

        return $attempts;
    }
}
