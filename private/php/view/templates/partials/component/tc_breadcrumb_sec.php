<?php 
    /* @var $section Ui\Section */
    /* @var $active Ui\Section */
    $active = $this->getActiveSection();
    if ($active !== null) : ?>
    <ol class="breadcrumb">
        <?php 
        $sec = $active;
        foreach ($active->getAllFromParentToChild() as $section) : ?>
            <li>
                <a 
                    class="<?=$section->equals($active) ? 'active' : ''?>"
                    href="<?=$this->e($this->getResource($section->getNavPath()))?>"<?=$this->e($sec->getNameI18n())?>
                    ><?=$this->egettext($section->getNameI18n())?></a>
            </li>
        <?php endforeach; ?>
    </ol>
<?php endif; ?>