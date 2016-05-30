<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Quote
 *
 * @ORM\Table(name="quotes", uniqueConstraints={@ORM\UniqueConstraint(name="uniq", columns={"date", "symbol"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\QuoteRepository")
 */
class Quote
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
     * @ORM\Column(name="open", type="decimal", precision=10, scale=4)
     */
    private $open;

    /**
     * @var string
     *
     * @ORM\Column(name="high", type="decimal", precision=10, scale=4)
     */
    private $high;

    /**
     * @var string
     *
     * @ORM\Column(name="low", type="decimal", precision=10, scale=4)
     */
    private $low;

    /**
     * @var string
     *
     * @ORM\Column(name="close", type="decimal", precision=10, scale=4)
     */
    private $close;

    /**
     * @var int
     *
     * @ORM\Column(name="volume", type="bigint")
     */
    private $volume;

    /**
     * @var string
     *
     * @ORM\Column(name="csv", type="string", length=255)
     */
    private $csv;


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
     * @return Quote
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
     * @return Quote
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
     * Set open
     *
     * @param string $open
     *
     * @return Quote
     */
    public function setOpen($open)
    {
        $this->open = $open;

        return $this;
    }

    /**
     * Get open
     *
     * @return string
     */
    public function getOpen()
    {
        return $this->open;
    }

    /**
     * Set high
     *
     * @param string $high
     *
     * @return Quote
     */
    public function setHigh($high)
    {
        $this->high = $high;

        return $this;
    }

    /**
     * Get high
     *
     * @return string
     */
    public function getHigh()
    {
        return $this->high;
    }

    /**
     * Set low
     *
     * @param string $low
     *
     * @return Quote
     */
    public function setLow($low)
    {
        $this->low = $low;

        return $this;
    }

    /**
     * Get low
     *
     * @return string
     */
    public function getLow()
    {
        return $this->low;
    }

    /**
     * Set close
     *
     * @param string $close
     *
     * @return Quote
     */
    public function setClose($close)
    {
        $this->close = $close;

        return $this;
    }

    /**
     * Get close
     *
     * @return string
     */
    public function getClose()
    {
        return $this->close;
    }

    /**
     * Set volume
     *
     * @param integer $volume
     *
     * @return Quote
     */
    public function setVolume($volume)
    {
        $this->volume = $volume;

        return $this;
    }

    /**
     * Get volume
     *
     * @return int
     */
    public function getVolume()
    {
        return $this->volume;
    }

    /**
     * Set csv
     *
     * @param string $csv
     *
     * @return Quote
     */
    public function setCsv($csv)
    {
        $this->csv = $csv;

        return $this;
    }

    /**
     * Get csv
     *
     * @return string
     */
    public function getCsv()
    {
        return $this->csv;
    }
}

