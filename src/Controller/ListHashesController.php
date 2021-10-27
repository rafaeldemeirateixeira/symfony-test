<?php

namespace App\Controller;

use App\Resource\HashesResource;
use App\Service\ListHashesService;
use Doctrine\Common\Annotations\Annotation\Query;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ListHashesController
{
    private ListHashesService $listHashesService;

    /**
     * @param ListHashesService $listHashesService
     */
    public function __construct(ListHashesService $listHashesService)
    {
        $this->listHashesService = $listHashesService;
    }
    
    /**
     * @param integer $request
     * @return JsonResponse
     */
    public function index(Request $request, int $page, int $size): JsonResponse
    {
        $filters = $request->query->get('filter');
        $hashes = $this->listHashesService->getAll($page, $size, $filters);
        return new JsonResponse(
            (new HashesResource($hashes))->response()
        );
    }
}
