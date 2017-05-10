<?php
    use League\Plates\Template\Template;
    use Moose\PlatesExtension\PlatesMooseExtension;
    /* @var $this Template|PlatesMooseExtension */
?>
<?php if ($section->isAvailableToUser($this->getUser())): ?>
    <li class="<?= $this->getActiveSection()->isChildOfOrSame($section) ? 'active' : ''?> nav_<?=$section->getId()?>">
        <a href="<?=$this->e($this->getResource($section->getNavPath()))?>"><?= $this->e($section->getName())?></a>
    </li>
<?php endif; ?>