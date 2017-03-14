<?php

require_once '../bootstrap.php';

use Gettext\Translations;

$languages = ["de", "en"];
$all = array();

for($i = 0; $i < sizeof($languages); ++$i) {
    $lang = $languages[$i];
    $cur = array();
    $me = dirname(__FILE__);
    $path = "$me/../../resource/locale/$lang/LC_MESSAGES/i18n.po";
    $trs = Translations::fromPoFile($path);
    foreach($trs->getIterator() as $t) {
        if (empty($t->getTranslation())) {
            echo "Translation for key $key and language $lang is empty.";
        }
        $key = $t->getOriginal();
        if ($i > 0) {
            if (!array_key_exists($key, $all)) {
                $l = $languages[$i-1];
                echo("<p>Key $key does not exist for language $l.</p>\n");
            }
        }
        $cur[$key] = true;
        $all[$key] = true;
    }
    if ($i > 0) {
        foreach ($all as $key => $_) {
            if (!array_key_exists($key, $cur)) {
                echo("<p>Key $key does not exist for language $lang.</p>\n");
            }
        }
    }
}

echo "<p>All done.</p>";