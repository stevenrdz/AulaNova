<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class ApiController extends AbstractController
{
    protected function validateDto(object $dto, ValidatorInterface $validator): ?JsonResponse
    {
        $errors = $validator->validate($dto);
        if (count($errors) === 0) {
            return null;
        }

        $messages = [];
        foreach ($errors as $error) {
            /** @var ConstraintViolationInterface $error */
            $messages[$error->getPropertyPath()][] = $error->getMessage();
        }

        return $this->json([
            'message' => 'Validation failed',
            'errors' => $messages,
        ], 422);
    }
}
