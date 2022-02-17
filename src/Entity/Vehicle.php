<?php

namespace App\Entity;

use App\Repository\VehicleRepository;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VehicleRepository::class)]
class Vehicle
{
    const STRING_MESSAGE = 'The parameter value is invalid or was not provided, should be a character string.';
    const INTEGER_MESSAGE = 'The parameter value is invalid or was not provided, shoul be an integer number.';
    const DECIMAL_MESSAGE = 'The parameter value is invalid or was not provided, shoul be a decimal number.';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'datetime')]
    #[Assert\Type('datetime')]
    private $date_added;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\Choice(choices:['used', 'new'], message:'The parameter value is invalid or was not provided, the valid values are "{{ choices }}."')]
    private $type;

    #[ORM\Column(type: 'decimal', precision: 22, scale: 2)]
    #[Assert\Type(type:'double', message:Vehicle::DECIMAL_MESSAGE)]
    private $msrp;

    #[ORM\Column(type: 'integer')]
    #[Assert\Type('int', message:Vehicle::INTEGER_MESSAGE)]
    private $year;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Type('string', message:Vehicle::STRING_MESSAGE)]
    private $make;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Type('string', message:Vehicle::STRING_MESSAGE)]
    private $model;

    #[ORM\Column(type: 'integer')]
    #[Assert\Type('int', message:Vehicle::INTEGER_MESSAGE)]
    private $miles;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Type('string', message:Vehicle::STRING_MESSAGE)]
    private $vin;

    #[ORM\Column(type: 'boolean')]
    #[Assert\Type('boolean')]
    private $deleted;

    public function __construct($data)
    {
        $this->setDateAdded(new \DateTime());
        $this->setType(!isset($data['type']) ?  : $data['type']);
        $this->setMsrp(!isset($data['msrp']) ?  : $data['msrp']);
        $this->setYear(!isset($data['year']) ?  : $data['year']);
        $this->setMake(!isset($data['make']) ?  : $data['make']);
        $this->setModel(!isset($data['model']) ?  : $data['model']);
        $this->setMiles(!isset($data['miles']) ?  : $data['miles']);
        $this->setVin(!isset($data['vin']) ? : $data['vin']);
        $this->setDeleted(false);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateAdded(): ?\DateTimeInterface
    {
        return $this->date_added;
    }

    public function setDateAdded($date_added): self
    {
        $this->date_added = $date_added;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType($type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getMsrp()
    {
        return $this->msrp;
    }

    public function setMsrp($msrp): self
    {
        $this->msrp = $msrp;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear($year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getMake(): ?string
    {
        return $this->make;
    }

    public function setMake($make): self
    {
        $this->make = $make;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel($model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getMiles(): ?int
    {
        return $this->miles;
    }

    public function setMiles($miles): self
    {
        $this->miles = $miles;

        return $this;
    }

    public function getVin(): ?string
    {
        return $this->vin;
    }

    public function setVin($vin): self
    {
        $this->vin = $vin;

        return $this;
    }

    public function getDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted($deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function jsonResponse()
    {
        return[
            'id' => $this->getId(),
            'added_date' => $this->getDateAdded(),
            'type' => $this->getType(),
            'msrp' => $this->getMsrp(),
            'year' => $this->getYear(),
            'make' => $this->getMake(),
            'model' => $this->getModel(),
            'miles' => $this->getMiles(),
            'vin' => $this->getVin(),
            'deleted' => $this->getDeleted(),
        ];
    }
}
