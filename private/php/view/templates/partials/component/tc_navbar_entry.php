<li class="<?= $this->getActiveSection()->isChildOfOrSame($section) ? 'active' : ''?>">
    <a href="<?=$this->e($this->getResource($section->getNavPath()))?>"><?= $this->egettext($section->getNameI18n())?></a>
</li>