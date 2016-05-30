<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Chariot
 *
 * @ORM\Table(name="chariot", uniqueConstraints={@ORM\UniqueConstraint(name="uniq", columns={"date", "symbol"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ChariotRepository")
 */
class Chariot
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
     * @ORM\Column(name="symbol", type="string", length=32)
     */
    private $symbol;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="ema20", type="decimal", precision=10, scale=4)
     */
    private $ema20;

    /**
     * @var string
     *
     * @ORM\Column(name="low40", type="decimal", precision=10, scale=4)
     */
    private $low40;

    /**
     * @var string
     *
     * @ORM\Column(name="high40", type="decimal", precision=10, scale=4)
     */
    private $high40;


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
     * @return Chariot
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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Chariot
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set ema20
     *
     * @param string $ema20
     *
     * @return Chariot
     */
    public function setEma20($ema20)
    {
        $this->ema20 = $ema20;

        return $this;
    }

    /**
     * Get ema20
     *
     * @return string
     */
    public function getEma20()
    {
        return $this->ema20;
    }

    /**
     * Set low40
     *
     * @param string $low40
     *
     * @return Chariot
     */
    public function setLow40($low40)
    {
        $this->low40 = $low40;

        return $this;
    }

    /**
     * Get low40
     *
     * @return string
     */
    public function getLow40()
    {
        return $this->low40;
    }

    /**
     * Set high40
     *
     * @param string $high40
     *
     * @return Chariot
     */
    public function setHigh40($high40)
    {
        $this->high40 = $high40;

        return $this;
    }

    /**
     * Get high40
     *
     * @return string
     */
    public function getHigh40()
    {
        return $this->high40;
    }
}

