<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ma
 *
 * @ORM\Table(name="ma", uniqueConstraints={@ORM\UniqueConstraint(name="uniq", columns={"date", "symbol"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MaRepository")
 */
class Ma
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
     * @ORM\Column(name="close", type="decimal", precision=10, scale=3)
     */
    private $close;

    /**
     * @var string
     *
     * @ORM\Column(name="ema20", type="decimal", precision=10, scale=3)
     */
    private $ema20;

    /**
     * @var string
     *
     * @ORM\Column(name="low40", type="decimal", precision=10, scale=3)
     */
    private $low40;

    /**
     * @var string
     *
     * @ORM\Column(name="high40", type="decimal", precision=10, scale=3)
     */
    private $high40;

    /**
     * @var string
     *
     * @ORM\Column(name="ma50", type="decimal", precision=10, scale=3)
     */
    private $ma50;

    /**
     * @var string
     *
     * @ORM\Column(name="ema15", type="decimal", precision=10, scale=3)
     */
    private $ema15;

    /**
     * @var string
     *
     * @ORM\Column(name="vol50", type="float")
     */
    private $vol50;

    /**
     * @var boolean
     *
     * @ORM\Column(name="uptrend", type="boolean", nullable=true)
     */
    private $uptrend;

    /**
     * @var boolean
     *
     * @ORM\Column(name="cross_low", type="boolean", nullable=true)
     */
    private $crossLow;

    /**
     * @var boolean
     *
     * @ORM\Column(name="cross_high", type="boolean", nullable=true)
     */
    private $crossHigh;

    /**
     * @var string
     *
     * @ORM\Column(name="vol_above_ave", type="boolean", nullable=true)
     */
    private $volup;

    /**
     * @var string
     *
     * @ORM\Column(name="vol_1m", type="boolean", nullable=true)
     */
    private $vol1m;

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
     * @return Ma
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
     * @return Ma
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
     * @return Ma
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
     * @return Ma
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
     * @return Ma
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

    /**
     * Set ma50
     *
     * @param string $ma50
     *
     * @return Ma
     */
    public function setMa50($ma50)
    {
        $this->ma50 = $ma50;

        return $this;
    }

    /**
     * Get ma50
     *
     * @return string
     */
    public function getMa50()
    {
        return $this->ma50;
    }

    /**
     * Set ema15
     *
     * @param string $ema15
     *
     * @return Ma
     */
    public function setEma15($ema15)
    {
        $this->ema15 = $ema15;

        return $this;
    }

    /**
     * Get ema15
     *
     * @return string
     */
    public function getEma15()
    {
        return $this->ema15;
    }

    /**
     * Set close
     *
     * @param string $close
     *
     * @return Ma
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

    public function setUptrend($uptrend)
    {
        $this->uptrend = $uptrend;

        return $this;
    }

    public function getUptrend()
    {
        return $this->uptrend;
    }

    public function setCrossLow($crossLow)
    {
        $this->crossLow = $crossLow;

        return $this;
    }

    public function getCrossLow()
    {
        return $this->crossLow;
    }

    public function setCrossHigh($crossHigh)
    {
        $this->crossHigh = $crossHigh;

        return $this;
    }

    public function getCrossHigh()
    {
        return $this->crossHigh;
    }

    public function setVol50($vol50)
    {
        $this->vol50 = $vol50;

        return $this;
    }

    public function getVol50()
    {
        return $this->vol50;
    }

    public function setVolup($volup)
    {
        $this->volup = $volup;

        return $this;
    }

    public function getVolup()
    {
        return $this->volup;
    }

    public function setVol1m($vol1m)
    {
        $this->vol1m = $vol1m;

        return $this;
    }

    public function getVol1m()
    {
        return $this->vol1m;
    }
}
