<?php

// src/MessageHandler/CheckActiveQuestionsMessageHandler.php
namespace App\MessageHandler;

use App\Message\CheckActiveQuestionsMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Pregunta;
use \DateTime;
use Psr\Log\LoggerInterface;

#[AsMessageHandler]
final class CheckActiveQuestionsMessageHandler
{
    private LoggerInterface $logger;
    private EntityManagerInterface $entityManager;

    // Inyección de LoggerInterface y EntityManagerInterface en el constructor
    public function __construct(LoggerInterface $logger, EntityManagerInterface $entityManager)
    {
        $this->logger = $logger;
        $this->entityManager = $entityManager;
    }

    public function __invoke(CheckActiveQuestionsMessage $message): void
    {
        $this->task();
    }

    public function task() {
        $this->logger->info('Test de log de activación/desactivación de preguntas');
        // Obtener la fecha actual
        $now = new DateTime();

        // Obtener todas las preguntas
        $preguntas = $this->entityManager->getRepository(Pregunta::class)->findAll();

        // Comprobar si la pregunta está activa según las fechas
        foreach ($preguntas as $pregunta) {
            $this->logger->info('Verificando pregunta ID: ' . $pregunta->getId());
            if ($now >= $pregunta->getFechaInicio() && $now <= $pregunta->getFechaFin()) {
                // Si está dentro del rango de fechas, la activamos
                if (!$pregunta->getActivo()) {
                    $pregunta->setActivo(true);
                    $this->logger->info('Pregunta activada ID: ' . $pregunta->getId());
                    
                }
            } else {
                // Si no está dentro del rango, la desactivamos
                if ($pregunta->getActivo()) {
                    $pregunta->setActivo(false);
                    $this->logger->info('Pregunta desactivada ID: ' . $pregunta->getId());
                }
            }
        }

        // Guardar los cambios en la base de datos
        $this->entityManager->flush();
    }
}
