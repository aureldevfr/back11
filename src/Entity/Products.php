<?php

namespace App\Entity;

use App\Repository\ProductsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductsRepository::class)
 */
class Products
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    public function getId():  ? int
    {
        return $this->id;
    }

    public function setId(int $id) : self
    {
        $this->id = $id;

        return $this;
    }

    // name
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    public function getName():  ? string
    {
        return $this->name;
    }

    public function setName(string $name) : self
    {
        $this->name = $name;

        return $this;
    }

    //description
    /**
     * @ORM\Column(type="text")
     */
    private $description;

    public function getDescription():  ? string
    {
        return $this->description;
    }

    public function setDescription(string $description) : self
    {
        $this->description = $description;

        return $this;
    }

    //photo
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $photo;

    public function getPhoto():  ? string
    {
        return $this->photo;
    }

    public function setPhoto(string $photo) : self
    {
        $this->photo = $photo;

        return $this;
    }

    //price
    /**
     * @ORM\Column(type="integer")
     */
    private $price;

    public function getPrice():  ? int
    {
        return $this->price;
    }

    public function setPrice(int $price) : self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @ORM\Column(type="boolean")
     */
    private $available;

    public function getAvailable():  ? bool
    {
        return $this->available;
    }

    public function setAvailable(bool $available) : self
    {
        $this->available = $available;

        return $this;
    }

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $category;

    public function getCategory():  ? string
    {
        return $this->category;
    }

    public function setCategory(string $category) : self
    {
        $this->category = $category;

        return $this;
    }

}
