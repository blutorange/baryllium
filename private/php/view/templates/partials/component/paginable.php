<?php
    /* @var $paginable Moose\ViewModel\PaginableInterface */
    $classesContainer = $classesContainer ?? '';
    $showPrevious = $showPrevious ?? true;
    $showNext = $showNext ?? true;
    $beforeCurrent = $beforeCurrent ?? -1;
    $afterCurrent = $afterCurrent ?? -1;
    $includeFirstN = $includeFirstN ?? 1;
    $includeLastN = $includeLastN ?? 1;
    if ($paginable === null || $paginable->getPaginablePageCount() < 2) {
        return;
    }
?>

<nav aria-label="<?=$this->egettext('navigation.label')?>" class="jscroll-paging <?=$classesContainer?>">
    <ul class="pagination">
        <?php if ($showPrevious) : ?>
            
            <?php if ($paginable->hasPaginablePrevious()): ?>
                <li class="nav-prev">
                    <a href="<?=$paginable->getPaginablePage($paginable->getPaginableCurrentPage()-1)?>" aria-label="<?=$this->egettext('navigation.previous')?>">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
            <?php else: ?>
                <li class="disabled">
                    <a href="#" aria-label="<?=$this->egettext('navigation.previous')?>">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>                    
            <?php endif; ?>
            
        <?php endif; ?>
            
        <?php foreach ($paginable->getPaginablePages($beforeCurrent, $afterCurrent, $includeFirstN, $includeLastN) as $ordinal => $href) { ?>
            <li
                <?php if ($ordinal === $paginable->getPaginableCurrentPage()): ?>
                    class="active"
                <?php endif; ?>
            >
                <a href="<?=$this->e($href)?>"><?=$this->e((string)$ordinal)?></a>
            </li>
        <?php } ?>
        
        <?php if ($showNext) : ?>
            <?php if ($paginable->hasPaginableNext()): ?>
                <li class="nav-next jscroll-next">
                    <a href="<?=$paginable->getPaginablePage($paginable->getPaginableCurrentPage()+1)?>" aria-label="<?=$this->egettext('navigation.next')?>">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
             <?php else: ?>
                <li class="disabled jscroll-destroy">
                    <a href="javascript:void()" aria-label="<?=$this->egettext('navigation.next')?>">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
             <?php endif; ?>
        <?php endif; ?>    
    </ul>
</nav>