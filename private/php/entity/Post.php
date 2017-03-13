<?php
namespace Entity;

use Entity\AbstractEntity;

/**
 * Entity for post on the message board.
 *
 * @Entity
 * @Table(name="post")
 * 
 * @author madgaksha
 */
class Post extends AbstractEntity {
    /**
     * @Column(type="string", length=64, unique=false, nullable=false)
     * @var string The title of this post.
     */    
    protected $title;

    public function getTitle() : string {
        return $this->title;
    }
    public function setTitle(string $title) {
        $this->title = $title;
    }   
}