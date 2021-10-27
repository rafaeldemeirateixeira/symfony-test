<?php

namespace App\Controller;

use App\Exception\ValidationException;
use App\Service\CreateHashService;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateHashController
{
    /** @var CreateHashService */
    private CreateHashService $createHashService;

    /** @var ValidatorInterface */
    private ValidatorInterface $validator;

    /**
     * @param CreateHashService $createHashService
     */
    public function __construct(
        CreateHashService $createHashService,
        ValidatorInterface $validator
    ) {
        $this->createHashService = $createHashService;
        $this->validator = $validator;
    }
    
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $this->validate($data);
    
            $hash = $this->createHashService->createHash($data, $request->getClientIp());
            
            return new JsonResponse([
                'hash' => $hash->getHash(),
                'key' => $hash->getKey(),
                'attempts' => $hash->getAttempts(),
            ], 201);
        } catch (ValidationException $e) {
            return new JsonResponse($e->response(), 400);
        } catch (HttpException $e) {
            return new JsonResponse($e->getMessage(), $e->getStatusCode(), $e->getHeaders());
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @param array $data
     * @return void
     * @throws ValidationException
     */
    private function validate(array $data): void
    {
        $constraints = new Assert\Collection([
            'input' => [
                new Assert\NotBlank()
            ],
            'block_number' => [
                new Assert\NotBlank(),
                new Assert\Type([
                    'type' => ['digit', 'integer'],
                ])
            ]
        ]);

        $validate = $this->validator->validate($data, $constraints);
        if ($validate->count()) {
            throw new ValidationException($validate);
        }
    }
}
