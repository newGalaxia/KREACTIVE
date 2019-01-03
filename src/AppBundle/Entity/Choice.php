<?php
namespace AppBundle\Entity;

use AppBundle\Entity\Film;
use AppBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ChoiceRepository")
 * @ORM\Table(name="choices", uniqueConstraints={ @ORM\UniqueConstraint(name="user_film_unique", columns={"user_id", "film_id"})})
 */
class Choice
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="Choices")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Film::class)
     */
    private $film;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return Film
     */
    public function getFilm()
    {
        return $this->film;
    }

    /**
     * @param Film $film
     */
    public function setFilm($film)
    {
        $this->film = $film;
    }
}