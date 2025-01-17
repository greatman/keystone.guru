<?php


namespace App\Logic\MDT\Entity;


class MDTNpc
{
    /** @var int */
    private $_index;

    /** @var array */
    private $_rawMdtNpc;

    /** @var array */
    private $_clones;

    /** @var int */
    private $_reaping;

    /** @var int */
    private $_id = 0;

    /** @var array */
    private $_spells = [];

    /** @var float */
    private $_scale = 0.0;

    /** @var int */
    private $_countTeeming = 0;

    /** @var int */
    private $_count = 0;

    /** @var string */
    private $_name;

    /** @var int */
    private $_displayId = 0;

    /** @var string */
    private $_creatureType;

    /** @var int */
    private $_level = 0;

    /** @var int */
    private $_health = 0;

    /** @var array */
    private $_characteristics = [];

    function __construct(int $index, array $rawMdtNpc)
    {
        $this->_index = $index;
        $this->_rawMdtNpc = $rawMdtNpc;
        $this->_clones = $rawMdtNpc['clones'];
        $this->_id = (int)$rawMdtNpc['id'];
        $this->_spells = isset($rawMdtNpc['spells']) ? $rawMdtNpc['spells'] : [];
        $this->_scale = (float)$rawMdtNpc['scale'];
        $this->_countTeeming = isset($rawMdtNpc['teemingCount']) ? (int)$rawMdtNpc['teemingCount'] : -1;
        $this->_count = (int)$rawMdtNpc['count'];
        // May not always be set?
        if (isset($rawMdtNpc['name'])) {
            $this->_name = $rawMdtNpc['name'];
        }
        $this->_displayId = (int)$rawMdtNpc['displayId'];
        // May not always be set?
        if (isset($rawMdtNpc['creatureType'])) {
            $this->_creatureType = $rawMdtNpc['creatureType'];
        }
        $this->_level = (int)$rawMdtNpc['level'];
        $this->_health = (int)$rawMdtNpc['health'];
        $this->_characteristics = isset($rawMdtNpc['characteristics']) ? $rawMdtNpc['characteristics'] : [];
    }

    /**
     * @return bool
     */
    public function isEmissary()
    {
        return in_array($this->_id, [155432, 155433, 155434]);
    }

    /**
     * @return bool
     */
    public function isAwakened()
    {
        return in_array($this->_id, [161244, 161243, 161124, 161241]);
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        // Skip emissaries
        return !$this->isEmissary();
    }

    /**
     * @return int
     */
    public function getIndex(): int
    {
        return $this->_index;
    }

    /**
     * @return array
     */
    public function getRawMdtNpc(): array
    {
        return $this->_rawMdtNpc;
    }

    /**
     * @return array
     */
    public function getClones(): array
    {
        return $this->_clones;
    }

    /**
     * @return int
     */
    public function getReaping(): int
    {
        return $this->_reaping;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->_id;
    }

    /**
     * @return array
     */
    public function getSpells(): array
    {
        return $this->_spells;
    }

    /**
     * @return float
     */
    public function getScale(): float
    {
        return $this->_scale;
    }

    /**
     * @return int
     */
    public function getCountTeeming(): int
    {
        return $this->_countTeeming;
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->_count;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->_name;
    }

    /**
     * @return int
     */
    public function getDisplayId(): int
    {
        return $this->_displayId;
    }

    /**
     * @return string|null
     */
    public function getCreatureType(): ?string
    {
        return $this->_creatureType;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->_level;
    }

    /**
     * @return int
     */
    public function getHealth(): int
    {
        return $this->_health;
    }

    /**
     * @return array
     */
    public function getCharacteristics(): array
    {
        return $this->_characteristics;
    }
}