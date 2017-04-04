<li class="<?= $this->getActiveSection()->isChildOfOrSame($section) ? 'active' : ''?>">
    <a href="<?=$this->e($this->getResource($section->getNavPath()))?>"><?= $this->e($section->getName())?></a>
</li>