<?php

namespace App\Service;

use App\Entity\Hashes;
use App\Repository\HashesRepository;
use DateInterval;
use DateTime;
use function Symfony\Component\String\u;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class CreateHashService
{
    /** @var HashesRepository */
    private HashesRepository $hashesRepository;

    /**
     * @param HashesRepository $hashesRepository
     */
    public function __construct(HashesRepository $hashesRepository)
    {
        $this->hashesRepository = $hashesRepository;
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

        //TODO: Gerar gerar key
        $key = $this->keyGenerate();
        $md5 = md5(u($data['input'])->append($key));
        
        $hash = [
            'input' => $data['input'],
            'hash' => u('0000')->append($md5),
            'key' => $key,
            'block_number' => $data['block_number'],
            'attempts' => 12,
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
}
