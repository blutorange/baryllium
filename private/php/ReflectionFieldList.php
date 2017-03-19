<?php

use Entity\Post;
use Entity\Thread;

/**
 * Description of ReflectionFieldList
 *
 * @author madgaksha
 */
class ReflectionFieldList {
    private static $THREAD_FOPRUM;
    private function __construct() {}
    public static function getThreadForum() : ReflectionProperty {
        if (self::$THREAD_FOPRUM === null) {
            self::$THREAD_FOPRUM= new ReflectionProperty(Thread::class, "forum");
            self::$THREAD_FOPRUM->setAccessible(true);
        }
        return self::$THREAD_FOPRUM;
    }

    public static function getPostThread() {
        if (self::$POST_THREAD === null) {
            self::$POST_THREAD= new ReflectionProperty(Post::class, "thread");
            self::$POST_THREAD->setAccessible(true);
        }
        return self::$POST_THREAD;
    }

}
