<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Atr
 *
 * @ORM\Table(name="atr", uniqueConstraints={@ORM\UniqueConstraint(name="uniq", columns={"date", "symbol"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AtrRepository")
 */
class Atr
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
     * @ORM\Column(name="value", type="decimal", precision=10, scale=4)
     */
    private $value;

    /**
     * @var boolean
     *
     * @ORM\Column(name="peak", type="boolean", nullable=true)
     */
    private $peak;

    /**
     * @var boolean
     *
     * @ORM\Column(name="bottom", type="boolean", nullable=true)
     */
    private $bottom;

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
     * @return Atr
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
     * @return Atr
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
     * Set value
     *
     * @param string $value
     *
     * @return Atr
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set peak
     *
     * @param string $peak
     *
     * @return Atr
     */
    public function setPeak($peak)
    {
        $this->peak = $peak;

        return $this;
    }

    /**
     * Get peak
     *
     * @return string
     */
    public function getPeak()
    {
        return $this->peak;
    }

    /**
     * Set bottom
     *
     * @param string $bottom
     *
     * @return Atr
     */
    public function setBottom($bottom)
    {
        $this->bottom = $bottom;

        return $this;
    }

    /**
     * Get bottom
     *
     * @return string
     */
    public function getBottom()
    {
        return $this->bottom;
    }
}
