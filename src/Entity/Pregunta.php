<?php

namespace App\Entity;

use App\Repository\PreguntaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PreguntaRepository::class)]
class Pregunta
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $enunciado = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fecha_inicio = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $fecha_fin = null;

    #[ORM\Column]
    private ?bool $activo = false;

    /**
     * @var Collection<int, Respuesta>
     */
    #[ORM\OneToMany(targetEntity: Respuesta::class, mappedBy: 'pregunta', orphanRemoval: true)]
    private Collection $respuestas;

    #[ORM\Column(length: 255)]
    private ?string $respuesta1 = null;

    #[ORM\Column(length: 255)]
    private ?string $respuesta2 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $respuesta3 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $respuesta4 = null;

    #[ORM\Column(length: 255)]
    private ?string $respuesta_correcta = null;

    public function __construct()
    {
        $this->respuestas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEnunciado(): ?string
    {
        return $this->enunciado;
    }

    public function setEnunciado(string $enunciado): static
    {
        $this->enunciado = $enunciado;

        return $this;
    }

    public function getFechaInicio(): ?\DateTimeInterface
    {
        return $this->fecha_inicio;
    }

    public function setFechaInicio(\DateTimeInterface $fecha_inicio): static
    {
        $this->fecha_inicio = $fecha_inicio;

        return $this;
    }

    public function getFechaFin(): ?\DateTimeInterface
    {
        return $this->fecha_fin;
    }

    public function setFechaFin(\DateTimeInterface $fecha_fin): static
    {
        $this->fecha_fin = $fecha_fin;

        return $this;
    }

    public function isActive(): bool
    {
        $now = new \DateTime(); // Fecha y hora actuales
        return $this->fecha_inicio <= $now && $this->fecha_fin >= $now;
    }

    public function getActivo(): ?bool
    {
        return $this->activo;
    }

    public function setActivo(bool $activo): static
    {
        $this->activo = $activo;

        return $this;
    }

    /**
     * @return Collection<int, Respuesta>
     */
    public function getRespuestas(): Collection
    {
        return $this->respuestas;
    }

    public function addRespuesta(Respuesta $respuesta): static
    {
        if (!$this->respuestas->contains($respuesta)) {
            $this->respuestas->add($respuesta);
            $respuesta->setPregunta($this);
        }

        return $this;
    }

    public function removeRespuesta(Respuesta $respuesta): static
    {
        if ($this->respuestas->removeElement($respuesta)) {
            // set the owning side to null (unless already changed)
            if ($respuesta->getPregunta() === $this) {
                $respuesta->setPregunta(null);
            }
        }

        return $this;
    }

    public function getRespuesta1(): ?string
    {
        return $this->respuesta1;
    }

    public function setRespuesta1(string $respuesta1): static
    {
        $this->respuesta1 = $respuesta1;

        return $this;
    }

    public function getRespuesta2(): ?string
    {
        return $this->respuesta2;
    }

    public function setRespuesta2(string $respuesta2): static
    {
        $this->respuesta2 = $respuesta2;

        return $this;
    }

    public function getRespuesta3(): ?string
    {
        return $this->respuesta3;
    }

    public function setRespuesta3(?string $respuesta3): static
    {
        $this->respuesta3 = $respuesta3;

        return $this;
    }

    public function getRespuesta4(): ?string
    {
        return $this->respuesta4;
    }

    public function setRespuesta4(?string $respuesta4): static
    {
        $this->respuesta4 = $respuesta4;

        return $this;
    }

    public function getRespuestaCorrecta(): ?string
    {
        return $this->respuesta_correcta;
    }

    public function setRespuestaCorrecta(string $respuesta_correcta): static
    {
        $this->respuesta_correcta = $respuesta_correcta;

        return $this;
    }
}
