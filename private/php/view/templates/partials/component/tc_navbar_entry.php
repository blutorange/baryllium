<?php
    use League\Plates\Template\Template;
    use Moose\PlatesExtension\PlatesMooseExtension;
    use Moose\ViewModel\SectionInterface;
    /* @var $this Template|PlatesMooseExtension */
    /* @var $section SectionInterface */
?>
<?php if ($section->isAvailableToUser($this->getUser())): ?>
    <li class="<?= $this->getActiveSection()->isChildOfOrSame($section) ? 'active' : ''?> nav_<?=$section->getId()?>">
        <a href="<?=$this->e($this->getResource($section->getNavPath()))?>"><?= $this->e($section->getName())?></a>
    </li>
<?php endif; ?>