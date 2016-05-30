<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Risky
 *
 * @ORM\Table(name="risky")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RiskyRepository")
 */
class Risky
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="symbol", type="string", length=32, unique=true)
     */
    private $symbol;

    /**
     * @var string
     *
     * @ORM\Column(name="current", type="decimal", precision=10, scale=4)
     */
    private $current;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set symbol
     *
     * @param string $symbol
     *
     * @return Risky
     */
    public function setSymbol($symbol)
    {
        $this->symbol = $symbol;

        return $this;
    }

    /**
     * Get symbol
     *
     * @return string
     */
    public function getSymbol()
    {
        return $this->symbol;
    }

    /**
     * Set current
     *
     * @param string $current
     *
     * @return Risky
     */
    public function setCurrent($current)
    {
        $this->current = $current;

        return $this;
    }

    /**
     * Get current
     *
     * @return string
     */
    public function getCurrent()
    {
        return $this->current;
    }
}

