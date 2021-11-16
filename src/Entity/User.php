<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=200)
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity=ToDo::class, mappedBy="author", orphanRemoval=true)
     */
    private $todo_list;

    public function __construct()
    {
        $this->todo_list = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return Collection|ToDo[]
     */
    public function getTodoList(): Collection
    {
        return $this->todo_list;
    }

    public function addTodoList(ToDo $todoList): self
    {
        if (!$this->todo_list->contains($todoList)) {
            $this->todo_list[] = $todoList;
            $todoList->setAuthor($this);
        }

        return $this;
    }

    public function removeTodoList(ToDo $todoList): self
    {
        if ($this->todo_list->removeElement($todoList)) {
            // set the owning side to null (unless already changed)
            if ($todoList->getAuthor() === $this) {
                $todoList->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            "email" => $this->getEmail(),
            "password" => $this->getPassword()

//            "todo_list" => [$this->getTodoList()]
        ];
    }
}
