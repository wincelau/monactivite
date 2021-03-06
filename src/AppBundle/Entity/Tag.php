<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tag
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Tag
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="color", type="string", length=255, nullable=true)
     */
    private $color;

    /**
     * @var string
     *
     * @ORM\Column(name="color_text", type="string", length=255, nullable=true)
     */
    private $colorText;

    /**
     * @var string
     *
     * @ORM\Column(name="icon", type="string", length=255, nullable=true)
     */
    private $icon;

    /**
     * @var string
     *
     * @ORM\Column(name="special", type="string", length=255, nullable=true)
     */
    private $special;

    const SPECIAL_DELETED = 'DELETED';
    const SPECIAL_TYPE = 'TYPE';

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function getKey() {

        return $this->getId()."-".$this->getName();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Tag
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set color
     *
     * @param string $color
     * @return Tag
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Get color
     *
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * Set icon
     *
     * @param string $icon
     * @return Tag
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get icon
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    public function __toString() {

        return $this->getName();
    }

    /**
     * Set colorText
     *
     * @param string $colorText
     * @return Tag
     */
    public function setColorText($colorText)
    {
        $this->colorText = $colorText;

        return $this;
    }

    /**
     * Get colorText
     *
     * @return string
     */
    public function getColorText()
    {
        if(!$this->colorText && $this->getColor()) {

            return '#fff';
        }

        return $this->colorText;
    }

    public function setSpecial($special)
    {
        $this->special = $special;

        return $this;
    }

    public function getSpecial() {

        return $this->special;
    }

    public function toConfig() {

        return array(
            'name' => $this->getName(),
            'color' => $this->getColor(),
            'color_text' => $this->getColorText(),
            'icon' => $this->getIcon(),
        );
    }

}
