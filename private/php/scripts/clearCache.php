<!DOCTYPE html>
<html><head><title>Clear Cache</title></head><body>

<?php

use Doctrine\ORM\EntityManagerInterface;
use Moose\Context\Context;

function clearCache($cacheDriver) {
    if ( ! $cacheDriver) {
        echo 'No Query cache driver is configured on given EntityManager.';
        return;
    }

    if ($cacheDriver instanceof ApcCache) {
        echo "Cannot clear APC Cache from Console, its shared in the Webserver memory and not accessible from the CLI.";
        return;
    }
    if ($cacheDriver instanceof XcacheCache) {
        echo "Cannot clear XCache Cache from Console, its shared in the Webserver memory and not accessible from the CLI.";
        return;
    }

    $result  = $cacheDriver->deleteAll();
    $message = ($result) ? 'Successfully deleted cache entries.' : 'No cache entries were deleted.';

    $result  = $cacheDriver->flushAll();
    $message = ($result) ? 'Successfully flushed cache entries.' : $message;

    echo $message;
}

require_once '../../bootstrap.php';

Context::getInstance()->getEm();
echo "<h1>Clearing MOOSE cache.</h1>";
clearCache(Context::getActualCache());
clearCache(Context::getInstance()->getCache());
echo "<h1>Clearing query cache.</h1>";
Context::getInstance()->withEm(null, function(EntityManagerInterface $em){
    clearCache($em->getConfiguration()->getQueryCacheImpl());
});
echo "<h1>Clearing metadata cache.</h1>";
Context::getInstance()->withEm(null, function(EntityManagerInterface $em){
    clearCache($em->getConfiguration()->getMetadataCacheImpl());
});
echo "<h1>Clearing result cache.</h1>";
Context::getInstance()->withEm(null, function(EntityManagerInterface $em){
    clearCache($em->getConfiguration()->getResultCacheImpl());
});
?>
<h1>Navigation</h1>
<a href="../../..">Proceed to main page.</a>
</body></html>