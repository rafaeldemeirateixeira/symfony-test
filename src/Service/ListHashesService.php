<?php

namespace App\Service;

use App\Entity\Hashes;
use App\Repository\HashesRepository;

class ListHashesService
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
     * @param integer $page
     * @param integer $size
     * @param mixed $filters
     * @return array
     */
    public function getAll(int $page, int $size, $filters = null): array
    {
        return $this->hashesRepository->getAll($page, $size, $filters);
    }
}
