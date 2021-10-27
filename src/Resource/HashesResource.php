<?php

namespace App\Resource;

use App\Entity\Hashes;

class HashesResource
{
    private array $resource;

    public function __construct(array $resource) {
        $this->resource = $resource;
    }

    /**
     * @param array $collection
     * @return array
     */
    public function collection(array $collection): array
    {
        return array_map(function (Hashes $item) {
            return [
                'batch' => $item->getBatch(),
                'block_number' => $item->getBlockNumber(),
                'input' => $item->getInput(),
                'hash' => $item->getHash(),
            ];
        }, $collection);
    }

    /**
     * @return array
     */
    public function response(): array
    {
        $this->resource['data'] = $this->collection($this->resource['data']);
        return $this->resource;
    }
}
