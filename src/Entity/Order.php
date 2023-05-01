<?php

namespace App\Entity;

use App\Entity\Products;
use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 * @ORM\Table(name="`order`")
 */
class Order
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $totalPrice;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private $creationDate;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->creationDate = new \DateTimeImmutable();
    }

    public function getId():  ? int
    {
        return $this->id;
    }

    public function setId(int $id) : self
    {
        $this->id = $id;

        return $this;
    }

    public function getTotalPrice():  ? int
    {
        return $this->totalPrice;
    }

    public function setTotalPrice(int $totalPrice) : self
    {
        $this->totalPrice = $totalPrice;

        return $this;
    }

    public function getCreationDate():  ? \DateTimeImmutable
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeImmutable $creationDate) : self
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getUser():  ? User
    {
        return $this->user;
    }

    public function setUser( ? User $user) : self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @ORM\ManyToMany(targetEntity=Products::class)
     * @ORM\JoinTable(name="order_products")
     */
    private $products;

    public function addProduct(Products $product) : self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
        }

        return $this;
    }

    public function removeProduct(Products $product): self
    {
        $this->products->removeElement($product);

        return $this;
    }

    public function getProducts(): Collection
    {
        return $this->products;
    }

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    public function getIsActive():  ? bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive) : self
    {
        $this->isActive = $isActive;

        return $this;
    }

}
