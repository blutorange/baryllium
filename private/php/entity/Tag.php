<?php

namespace Entity;

use Doctrine\ORM\Mapping\Column;
use Ui\PlaceholderTranslator;

/**
 * A tag. Each entity may be tagged with several tags.
 *
 * @author madgaksha
 */
class Tag extends AbstractEntity {

    /**
     * @Column(name="name", type="string", length=32, unique=false, nullable=false)
     * @var string The name of this tag, eg. <code>maths</code>.
     */
    protected $name;
    private static $MAX_LENGTH_TAGNAME;

    public function validate(array & $errMsg, PlaceholderTranslator $translator): bool {
        $valid = true;
        $valid = $valid && $this->validateNonEmptyStringLength($this->name,
                        self::$MAX_LENGTH_TAGNAME, $errMsg, $translator,
                        'error.validation', 'error.tag.name.empty',
                        'error.tag.name.overlong');
        return $valid;
    }
}
