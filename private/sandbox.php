<?php

namespace Sandbox;

use DateTime;
use Extension\DiningHall\MensaJohannstadtLoader;

/* Use this for quickly testing some php code... */

require_once './bootstrap.php';

$from = new DateTime();
$from->modify('-3 day');
$to = new DateTime();

$loader = new MensaJohannstadtLoader();
$meals = $loader->fetchMenu($from, $to, false);
var_dump($loader->getName());
var_dump($loader->getLocation());
var_dump($meals);

//
////class Abc {
//    /**
//     * @Column(name="ME", type="text", length=32, unique=true, nullable=false)
//     * @Assert\Length(min=5, max=9);
//     * @References("Entity\User::pwdhash")
//     * @var type 
//     */
//    protected $me;
//    
//    /**
//     * @Assert\NotBlank
//     */
//    protected $you;
//}
//
//$reader = new AnnotationReader();
//var_dump(ReflectionCache::getPropertiesAnnotations(Abc::class));


//apcu_clear_cache();
//$model = new UserFormModel();
//$view = new \View\FormView($model, "partials/form/generic_form");
//$messages = [];
//$model->processPost($_GET, new \Ui\PlaceholderTranslator("de"), $messages);
//
//var_dump($model->getClassInstance(\Entity\User::class));
//
//echo $view->render($context->getEngine());
//$fields = $model->getFormFields();
//echo $fields[0]->getView()->render($context->getEngine(), $fields[0]);
//var_dump(Key::createNewRandomKey()->saveToAsciiSafeString());
//$enc = \EncryptionUtil::encryptForDatabase("11035cre&$!");
//var_dump($enc);
//var_dump(\EncryptionUtil::decryptFromDatabase("asds"));

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
?>