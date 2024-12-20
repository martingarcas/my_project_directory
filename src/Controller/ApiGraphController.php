<?php

namespace App\Controller;

use App\Repository\RespuestaRepository;
use App\Repository\PreguntaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiGraphController extends AbstractController
{
    #[Route('/api/graph', name: 'app_api_graph')]
    public function index(RespuestaRepository $respuestaRepository, PreguntaRepository $preguntaRepository): JsonResponse
    {
        // Obtener la pregunta activa
        $preguntaActiva = $preguntaRepository->findOneBy(['activo' => true]);

        if (!$preguntaActiva) {
            return new JsonResponse(['error' => 'No hay pregunta activa'], 404);
        }

        // Obtener todas las respuestas a la pregunta activa
        $respuestas = $respuestaRepository->findBy(['pregunta' => $preguntaActiva]);

        // Contar cuántas veces se ha seleccionado cada respuesta
        $respuestasCount = [
            $preguntaActiva->getRespuesta1() => 0,
            $preguntaActiva->getRespuesta2() => 0,
            $preguntaActiva->getRespuesta3() => 0,
            $preguntaActiva->getRespuesta4() => 0,
        ];

        foreach ($respuestas as $respuesta) {
            $respuestasCount[$respuesta->getRespuesta()]++;
        }

        // Retornar los datos en formato JSON
        return new JsonResponse($respuestasCount);
    }
}
