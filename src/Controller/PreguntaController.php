<?php

namespace App\Controller;

use App\Entity\Pregunta;
use App\Entity\Respuesta;
use App\Form\PreguntaType;
use App\Form\RespuestaType;
use App\Repository\PreguntaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\RespuestaRepository;

class PreguntaController extends AbstractController
{

    private LoggerInterface $logger;

    // Inyección de LoggerInterface y EntityManagerInterface en el constructor
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    #[Route('/admin/pregunta/create', name: 'create_pregunta')]
    public function createPregunta(Request $request, EntityManagerInterface $entityManager, PreguntaRepository $preguntaRepository): Response
    {
        $pregunta = new Pregunta();
        $form = $this->createForm(PreguntaType::class, $pregunta);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Lógica para verificar si las fechas se solapan
            if ($this->checkDateOverlap($pregunta, $preguntaRepository)) {
                $this->addFlash('error', 'Las fechas de la pregunta se solapan con otra pregunta existente.');
                return $this->render('admin/pregunta/create.html.twig', [
                    'form' => $form->createView(),
                ]); // Aquí no rediriges, simplemente renderizas con los valores ya introducidos
            }

            // Lógica para determinar si la pregunta debe ser activa
            $now = new \DateTime(); // Obtener la fecha y hora actual
            if ($now >= $pregunta->getFechaInicio() && $now <= $pregunta->getFechaFin()) {
                $pregunta->setActivo(true); // La pregunta está activa
                
            } else {
                $pregunta->setActivo(false); // La pregunta no está activa
            }

            // Guardar la nueva pregunta en la base de datos
            $entityManager->persist($pregunta);
            $entityManager->flush();

            return $this->redirectToRoute('admin_preguntas_list'); // Aquí deberías tener una ruta válida para la lista
        }

        return $this->render('admin/pregunta/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Método para mostrar todas las preguntas
    #[Route('/admin/pregunta/list', name: 'admin_preguntas_list')]
    public function listPreguntas(PreguntaRepository $preguntaRepository): Response
    {
        // Obtener todas las preguntas usando el repositorio
        //$preguntas = $preguntaRepository->findAll();
        // Obtener todas las preguntas ordenadas por fecha_inicio
        $preguntas = $preguntaRepository->findBy([], ['fecha_inicio' => 'ASC']);  // Orden ascendente

        // Renderizar la vista y pasar las preguntas
        return $this->render('admin/pregunta/list.html.twig', [
            'preguntas' => $preguntas,
        ]);
    }

    #[Route('/admin/pregunta/editar/{id}', name: 'editar_pregunta')]
    public function editarPregunta(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $pregunta = $entityManager->getRepository(Pregunta::class)->find($id);

        if(!$pregunta) {
            throw $this->createNotFoundException(
                "No se encontró la pregunta."
            );
        }


        $form = $this->createForm(PreguntaType::class, $pregunta);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Lógica para determinar si la pregunta debe ser activa
            $now = new \DateTime(); // Obtener la fecha y hora actual
            if ($now >= $pregunta->getFechaInicio() && $now <= $pregunta->getFechaFin()) {
                $pregunta->setActivo(true); // La pregunta está activa
                
            } else {
                $pregunta->setActivo(false); // La pregunta no está activa
            }

            // Guardar la nueva pregunta en la base de datos
            $entityManager->persist($pregunta);
            $entityManager->flush();

            return $this->redirectToRoute('admin_preguntas_list'); // Aquí deberías tener una ruta válida para la lista
        }

        return $this->render('admin/pregunta/editar.html.twig', [
            'form' => $form->createView(),
            'pregunta' => $pregunta,
        ]);
    }

    #[Route('/admin/pregunta/borrar/{id}', name: 'borrar_pregunta')]
    public function borrarPregunta(int $id, EntityManagerInterface $entityManager): Response
    {
        $pregunta = $entityManager->getRepository(Pregunta::class)->find($id);

        if(!$pregunta) {
            throw $this->createNotFoundException(
                "No se encontró la pregunta."
            );
        }

        // Guardar la nueva pregunta en la base de datos
        $entityManager->remove($pregunta);
        #$entityManager->persist($pregunta);
        $entityManager->flush();

        return $this->redirectToRoute('admin_preguntas_list'); // Aquí deberías tener una ruta válida para la lista
    }

     // Ruta para ver la pregunta activa
    #[Route('/user/pregunta/ver_pregunta', name: 'ver_pregunta')]
    public function verPregunta(PreguntaRepository $preguntaRepository): Response
    {
        // Obtener la pregunta activa
        $preguntaActiva = $preguntaRepository->findOneBy(['activo' => true]);

        if (!$preguntaActiva) {
            // Si no hay ninguna pregunta activa
            $this->addFlash('error', 'No hay ninguna pregunta activa para ver.');
            return $this->redirectToRoute('home');
        }

        return $this->render('/user/pregunta/ver_pregunta.html.twig', [
            'pregunta' => $preguntaActiva,
        ]);
    }

    #[Route('/user/pregunta/responder_pregunta', name: 'responder_pregunta', methods: ['POST'])]
    public function responderPregunta(
        Request $request,
        PreguntaRepository $preguntaRepository,
        EntityManagerInterface $entityManager,
        UserInterface $user, // para obtener el usuario autenticado
        SessionInterface $session // Inyección del servicio de sesión
    ): Response
    {
        // Obtener la pregunta activa
        $preguntaActiva = $preguntaRepository->findOneBy(['activo' => true]);

        if (!$preguntaActiva) {
            // Si no hay ninguna pregunta activa
            $this->addFlash('error', 'No hay ninguna pregunta activa para responder.');
            return $this->redirectToRoute('home');
        }

        // Comprobar si el usuario ya ha respondido a esta pregunta
        $respuestaExistente = $entityManager->getRepository(Respuesta::class)->findOneBy([
            'usuario' => $user,
            'pregunta' => $preguntaActiva
        ]);

        if ($respuestaExistente) {
            // Si el usuario ya ha respondido a esta pregunta
            return $this->render('user/pregunta/responder_pregunta.html.twig', [
                'mensaje' => 'Ya has respondido a la pregunta activa con anterioridad, no puedes hacerlo dos veces.',
                'respuesta_correcta' => null // No mostramos si es correcta o incorrecta
            ]);
        }

        // Obtener la respuesta seleccionada
        $respuestaSeleccionada = $request->get('respuesta');

        if (!$respuestaSeleccionada) {
            $this->addFlash('error', 'Debes seleccionar una respuesta.');
            return $this->redirectToRoute('ver_pregunta');
        }

        // Crear una nueva instancia de la entidad Respuesta
        $respuesta = new Respuesta();
        $respuesta->setUsuario($user); // Usuario autenticado
        $respuesta->setPregunta($preguntaActiva); // Pregunta activa
        $respuesta->setTimestamp(new \DateTime()); // Timestamp actual
        $respuesta->setRespuesta($respuestaSeleccionada); // Respuesta seleccionada por el usuario

        // Persistir la respuesta en la base de datos
        $entityManager->persist($respuesta);
        $entityManager->flush();

        // Verificar si la respuesta seleccionada es correcta
        $esCorrecta = $respuestaSeleccionada === $preguntaActiva->getRespuestaCorrecta();

        // Almacenar el resultado en la sesión usando el servicio inyectado
        $session->set('respuesta_correcta', $esCorrecta);

        // Redirigir a la vista con el resultado de la respuesta
        return $this->render('user/pregunta/responder_pregunta.html.twig', [
            'respuesta_correcta' => $esCorrecta,
            'mensaje' => null // No es necesario enviar un mensaje si no ha sido respondida previamente
        ]);
    }

    #[Route('/ver_respuestas', name: 'ver_respuestas')]
    public function verRespuestas(
        PreguntaRepository $preguntaRepository,
        RespuestaRepository $respuestaRepository
    ): Response {
        
        $preguntaActiva = $preguntaRepository->findOneBy(['activo' => true]);

        if (!$preguntaActiva) {
            $this->addFlash('error', 'No hay ninguna pregunta activa.');
            return $this->redirectToRoute('home');
        }

        $respuestas = $respuestaRepository->findBy(['pregunta' => $preguntaActiva]);

        return $this->render('/ver_respuestas.html.twig', [
            'pregunta' => $preguntaActiva,
            'respuestas' => $respuestas,
        ]);
    }
    

    /**
    * Verifica si las fechas de la nueva pregunta se solapan con alguna pregunta existente.
    *
    * @param Pregunta $pregunta
    * @param PreguntaRepository $preguntaRepository
    * @return bool
    */
    private function checkDateOverlap(Pregunta $pregunta, PreguntaRepository $preguntaRepository): bool
    {
        $fechaInicio = $pregunta->getFechaInicio();
        $fechaFin = $pregunta->getFechaFin();

        // Buscar preguntas que estén activas o que tengan fechas solapadas con la nueva pregunta
        $preguntas = $preguntaRepository->createQueryBuilder('p')
            ->where('p.fecha_inicio < :fechaFin')
            ->andWhere('p.fecha_fin > :fechaInicio')
            ->setParameter('fechaInicio', $fechaInicio)
            ->setParameter('fechaFin', $fechaFin)
            ->getQuery()
            ->getResult();

        return count($preguntas) > 0;
    }

}