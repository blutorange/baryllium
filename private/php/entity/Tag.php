<?php

namespace Entity;

use Doctrine\ORM\Mapping\Column;
use Ui\PlaceholderTranslator;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A tag. Each entity may be tagged with several tags.
 * @Entity
 * @Table(name="tag")
 * @author madgaksha
 */
class Tag extends AbstractEntity {

    /**
     * @Column(name="_name", type="string", length=32, unique=false, nullable=false)
     * @Assert\NotBlank(message="tag.name.empty")
     * @Assert\Length(max=32, message="tag.name.maxlength")
     * @var string The name of this tag, eg. <code>maths</code>.
     */
    protected $name;
    
    /**
     * @Column(name="creationdate", type="date", unique=false, nullable=false)
     * @Assert\NotNull(message="tag.creationdate.empty")
     * @Assert\Length(max=32, message="tag.name.maxlength")
     * @var DateTime When this tag was created.
     */
    protected $creationDate;
    
    public function getName() : string {
        return $this->name;
    }

    public function getCreationDate(): DateTime {
        return $this->creationDate;
    }

    public function setName(string $name) {
        $this->name = $name;
    }

    public function setCreationDate(DateTime $creationDate) {
        $this->creationDate = $creationDate;
    }
}
