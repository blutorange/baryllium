<?php
    use League\Plates\Template\Template;
    use Moose\ViewModel\SectionBasic;
    /* @var $this Template */
    $this->layout('portal', ['title' => $title ?? 'Import FieldOfStudy to courses']);
    $this->setActiveSection(SectionBasic::$IMPORT_FOS);
?>

<h1>Field of study list </h1>
<ul>
    <?php foreach ($foslist ?? [] as $fos) { ?>
        <li class="field-of-study"><?= $fos->getDiscipline()?>, <?= $fos->getSubDiscipline()?>
            <ul>
                <?php
                    $courseList = $fos->getCourseList();
                    $l = \Moose\Util\CollectionUtil::sortByField($courseList, 'name')
                ?>
                <?php foreach ($l as $course) { ?>
                    <li><?=$course->getName()?></li>
                <?php } ?>            
            </ul>
        </li>
    <?php } ?>
        
</ul>

<form id="setup_import_form" novalidate method="post" data-bootstrap-parsley enctype="multipart/form-data" action="<?= $this->e($action ?? $selfUrl ?? $_SERVER['PHP_SELF']) ?>">
    <input type="file" name="importcss"/>
    <div class="">
        <button id="password" class="btn btn-primary" name="btnSubmit" type="submit">
            <?= $this->egettext('register.submit') ?>
        </button>
    </div>    
</form>
