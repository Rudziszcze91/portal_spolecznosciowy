<?php
/**
 * Friend
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FriendRepository")
 */
class Friend
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $accepted;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="fromFriends")
     * @ORM\JoinColumn(nullable=false)
     */
    private $fromUser;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="toFriends")
     * @ORM\JoinColumn(nullable=false)
     */
    private $toUser;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return bool|null
     */
    public function getAccepted(): ?bool
    {
        return $this->accepted;
    }

    /**
     * @param bool $accepted
     *
     * @return $this
     */
    public function setAccepted(bool $accepted): self
    {
        $this->accepted = $accepted;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getFromUser(): ?User
    {
        return $this->fromUser;
    }

    /**
     * @param User|null $fromUser
     *
     * @return $this
     */
    public function setFromUser(?User $fromUser): self
    {
        $this->fromUser = $fromUser;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getToUser(): ?User
    {
        return $this->toUser;
    }

    /**
     * @param User|null $toUser
     *
     * @return $this
     */
    public function setToUser(?User $toUser): self
    {
        $this->toUser = $toUser;

        return $this;
    }
}
