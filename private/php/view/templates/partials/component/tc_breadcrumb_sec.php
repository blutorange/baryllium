<?php 
    /* @var $this League\Plates\Template\Template|\Moose\PlatesExtension\MainExtension */
    /* @var $section Moose\ViewModel\SectionInterface */
    /* @var $active Moose\ViewModel\SectionInterface */
    $active = $this->getActiveSection();
    if ($active !== null) : ?>
    <ol class="breadcrumb">
        <?php 
        $sec = $active;
        foreach ($active->getAllFromParentToChild() as $section) : ?>
            <li>
                <a 
                    class="<?=$section->equals($active) ? 'active' : ''?>"
                    href="<?=$this->e($this->getResource($section->getNavPath()))?>"<?=$this->e($sec->getName($this->getTranslator()))?>
                    ><?=$this->e($section->getName($this->getTranslator()))?></a>
            </li>
        <?php endforeach; ?>
    </ol>
<?php endif; ?>