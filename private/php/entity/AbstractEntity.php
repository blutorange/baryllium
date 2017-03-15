<?php

namespace Entity;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Ui\Message;
use Ui\PlaceholderTranslator;

/**
 * Base entity with an id.
 * 
 * @author madgaksha
 */
class AbstractEntity {    
    
    public static $INVALID_ID = -1;
    public static $INITIAL_ID = 0;

    /**
     * @Id
     * @Column(type="integer", length=32, unique=true, nullable=false)
     * @GeneratedValue
     * @var int
     */
    protected $id = 0;

    /**
     * @param arry $errMsg Array with error messages to append to.
     * @return bool Whether this entity validates standalone.
     */
    public function validate(array & $errMsg, PlaceholderTranslator $translator) : bool {
        return true;
    }
    
    /**
     * @param arry $errMsg Array with error messages to append to.
     * @param locale CUrrent locale to use for the error messages.
     * @param em Entity manager for the context.
     * @return bool Whether this entity validates within a context of other entities. No need to repeat what validate did.
     */
    public function validateMore(array & $errMsg, EntityManager $em, PlaceholderTranslator $translator) : bool {
        return true;
    }

    public function getId() : int {
        return $this->id;
    }
    public function setId(int $id) {
        $this->id = $id;
    }

    protected function validateStringLength($value, int $maxLength,
            array & $errMsg, PlaceholderTranslator $translator, string $i18nKey,
            string $i18nOverlong) : bool {
        if (strlen($value) > $maxLength) {
            array_push($errMsg,
                    Message::dangerI18n($i18nKey, $i18nOverlong, $translator,
                            ['count' => self::$MAX_LENGTH_NAME]));
            return false;
        }
        return true;
    }

    protected function validateNonEmpty($value, array & $errMsg,
            PlaceholderTranslator $translator, string $i18nKey,
            string $i18nEmpty) : bool {
        if (empty($value)) {
            array_push($errMsg,
                    Message::dangerI18n($i18nKey, $i18nEmpty, $translator));
            return false;
        }
        return true;
    }
    
    protected function validateNonNull($value, array & $errMsg,
            PlaceholderTranslator $translator, string $i18nKey,
            string $i18nEmpty) : bool {
        if ($value === null) {
            array_push($errMsg,
                    Message::dangerI18n($i18nKey, $i18nEmpty, $translator));
            return false;
        }
        return true;
    }
    
    protected function validateNonEmptyArray($value, array & $errMsg,
            PlaceholderTranslator $translator, string $i18nKey,
            string $i18nEmpty) : bool {
        if (sizeof($value) === 0) {
            array_push($errMsg,
                    Message::dangerI18n($i18nKey, $i18nEmpty, $translator));
            return false;
        }
        return true;
    }
    
    protected function validateNonEmptyStringLength($value, int $maxLength,
            array & $errMsg, PlaceholderTranslator $translator, string $i18nKey,
            string $i18nEmpty, string $i18nOverlong) : bool {
        if (!$this->validateNonEmpty($value, $errMsg, $translator, $i18nKey, $i18nEmpty)) {
            return false;
        }
        if (!$this->validateStringLength($value, $maxLength, $errMsg, $translator, $i18nKey, $i18nOverlong)) {
                return false;
        }
        return true;
    }
}