<?php $this->layout('setup', ['title' => $title ?? 'Import FieldOfStudy to courses']) ?>

<h1><?=$asd?></h1>
<h1>Field of study list </h1>
<ul>
    <?php foreach ($foslist ?? [] as $fos) { ?>
        <li><?= $fos->getDiscipline()?>, <?= $fos->getSubDiscipline()?>
            <ul>
                <?php foreach ($fos->getCourseList() as $course) { ?>
                    <li><?=$course->getName()?></li>
                <?php } ?>            
            </ul>
        </li>
    <?php } ?>
</ul>

<form novalidate method="post" data-bootstrap-parsley enctype="multipart/form-data" action="<?= $this->e($action ?? $selfUrl ?? $_SERVER['PHP_SELF']) ?>">
    <input type="file" name="importcss"/>
    <div class="">
        <button id="password" class="btn btn-primary" name="btnSubmit" type="submit">
            <?= $this->egettext('register.submit') ?>
        </button>
    </div>    
</form>