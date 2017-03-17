<?php

namespace Model;

use Dao\StudyGroupDao;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Entity\AbstractEntity;
use InvalidArgumentException;
use Ui\Message;
use Ui\PlaceholderTranslator;

/**
 * A study group (Seminargruppe) parsed into its parts.
 * 
 * @author Andre Wachsmuth
 */
// * @Entity
// * @Table(name="studygroup")
class StudyGroup extends AbstractEntity {
    const IDENTIFIER_LENGTH = 7;
    
    /**
     * @Column(type="integer", nullable=false)
     * @var string University type, eg. 3 for BA Dresden.
     */
    protected $universityType;
    
    /**
     * @Column(type="string", nullable=false)
     * @var string Name (shortcut) of the study group, eg. MI.
     */
    protected $groupName;
    
    /**
     * @Column(type="integer", nullable=false)
     * @var string The year of this study group, eg 2015.
     */
    protected $year;

    /**
     * @Column(type="integer", nullable=false)
     * @var string There may be several study groups per year, so this is their index. Eg 3.
     */
    protected $index;
    
    public function __construct(int $universityType, string $groupName, int $year, int $index) {
        $this->groupName = $groupName;
        $this->index = $index;
        $this->universityType = $universityType;
        $this->year = $year;
    }
    
    public function validate(array & $errMsg, PlaceholderTranslator $translator): bool {
        $valid = true;
        // TODO
        return $valid;
    }

    public function validateMore(array & $errMsg, EntityManager $em,
            PlaceholderTranslator $translator): bool {
        $valid = true;
        // TODO
        return $valid;
    }
        
    public static function valueOf(string $raw) {
        $data = trim($raw);
        $len = strlen($data);
        if ($len !== self::IDENTIFIER_LENGTH) {
            throw new InvalidArgumentException("Expected identifier $data to consist of exactly seven characters, but found $len.");
        }
        $rawUniversityType = substr($data, 0, 1);
        if (!is_numeric($rawUniversityType)) {
            throw new InvalidArgumentException("Expected university part of $data to be a number.");
        }
        $rawYear = substr($data, 3, 2);
        if (!is_numeric($rawYear)) {
            throw new InvalidArgumentException("Expected year part of $data to be a number.");
        }
        $rawIndex = substr($data, 6, 1);
        if (!is_numeric($rawIndex)) {
            throw new InvalidArgumentException("Expected index part of $data to be a number.");
        }
        return new StudyGroup(intval($rawUniversityType), substr($data, 1, 2), intval($rawYear) + 2000, intval($rawIndex));
    }
}
