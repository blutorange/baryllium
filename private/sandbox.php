<?php

namespace Sandbox;

/* Use this for quickly testing some php code... */

require_once './bootstrap.php';

use Defuse\Crypto\Key;


//var_dump(Key::createNewRandomKey()->saveToAsciiSafeString());
$enc = \EncryptionUtil::encryptForDatabase("11035cre&$!");
var_dump($enc);
var_dump(\EncryptionUtil::decryptFromDatabase("asds"));

//class Person
//{
//    /**
//     * @Assert\Length(min=5, max=5, exactMessage="error.length")
//     * @Assert\NotNull(message = "person.siblings.empty")
//     */
//    protected $siblings = "nl";
//
//    /**
//     * @Assert\GreaterThan(
//     *     value = 18
//     * )
//     */
//    protected $age = 0;
//    
//    public function getClass() {
//        return get_class($this);
//    }
//}
//
//class Translator implements \Symfony\Component\Translation\TranslatorInterface {
//    private $locale;
//    public function getLocale(): string {
//        return $locale;
//    }
//
//    public function setLocale($locale) {
//        $this->locale = $locale;
//    }
//
//    public function trans($id, array $parameters = array(), $domain = null,
//            $locale = null): string {
//        var_dump("id", $id);
//        var_dump("parameters", $parameters);
//        var_dump("domain", $domain);
//        var_dump("locale", $locale);
//        return "Whatever";
//    }
//
//    public function transChoice($id, $number, array $parameters = array(),
//            $domain = null, $locale = null): string {
//        var_dump("id", $id);
//        var_dump("parameters", $parameters);
//        var_dump("domain", $domain);
//        var_dump("locale", $locale);
//        return "HelloWolrd";
//    }
//
//}
//
//$c = new Person();
//
//$b = (new ValidatorBuilder());
//$v = $b->enableAnnotationMapping()->setTranslationDomain("validators")->setTranslator(new Translator)->getValidator()->validate($c);
//
//foreach ($v as $violation) {
//    var_dump($violation);
//}
//
//var_dump($c);



//CampusDualLoader::perform("3002591", "secretPassword", function(CampusDualLoader $loader) {
////    $start = time();
////    $end = time()+7*24*60*60;
////    var_dump($loader->getTimeTableRaw($start, $end));
////    var_dump($loader->getMetaRaw());
//    var_dump($loader->getStudyGroup());
//});