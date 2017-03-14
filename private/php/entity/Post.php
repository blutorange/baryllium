<?php

namespace Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Table;
use Entity\AbstractEntity;
use Ui\Message;
use Ui\PlaceholderTranslator;

/**
 * Entity for post on the message board.
 *
 * @Entity
 * @Table(name="post")
 * 
 * @author CaptainMalzbier
 */
class Post extends AbstractEntity {

    /**
     * @Column(type="string", length=255, unique=false, nullable=false)
     * @var string The title of this post.
     */
    private static $MAX_LENGTH_TITLE = 255;
    protected $title;

    public function getTitle(): string {
        return $this->title;
    }

    public function setTitle(string $title) {
        $this->title = $title;
    }

    public function validate(array & $errMsg, PlaceholderTranslator $translator): bool {
        $valid = true;
        if (empty($this->title)) {
            array_push($errMsg,
                    Message::dangerI18n('error.validation',
                            'error.post.name.empty', $translator));
            $valid = false;
        }
        else if (strlen($this->title) > self::$MAX_LENGTH_TITLE) {
            array_push($errMsg,
                    Message::dangerI18n('error.validation',
                            'error.post.title.overlong', $translator,
                            ['count' => self::$MAX_LENGTH_TITLE]));
            $valid = false;
        }
        return $valid;
    }

}
