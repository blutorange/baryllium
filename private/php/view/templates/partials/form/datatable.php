<?php
    use Moose\ViewModel\DataTableBuilderInterface;
    use Moose\ViewModel\DataTableColumnInterface;
    use Moose\ViewModel\DataTableInterface;
    /* @var $myTable DataTableInterface */
    /* @var $myColumn DataTableColumnInterface */
    $myTable = $table instanceof DataTableBuilderInterface ? $table->build() : $table;
?>

<table id="<?=$myTable->getId()?>"
        data-search-delay="<?=$myTable->getSearchDelay()?>"
        data-order-initial="<?=$myTable->getInitialOrderColumnIndex()?>"
        data-order-initial-dir="<?=$myTable->getIsInitialOrderAscending() ? 'asc' : 'desc'?>"
        data-url="<?=$this->e($myTable->getUrl())?>"
        data-action="<?=$this->e($myTable->getAction())?>"
        data-ordering="<?=$myTable->getIsOrderable() ? 'true' : 'false'?>"
        data-searching="<?=$myTable->getIsSearchable() ? 'true' : 'false'?>"
        data-paging="<?=$myTable->getIsPaginable() ? 'true' : 'false'?>"
        class="table table-striped table-bordered moose-datatable"
        cellspacing="0" width="100%">
    <thead>
        <tr>
            <?php foreach($myTable->getColumns() as $myColumn): ?>
                <th
                    data-type="<?=$this->e($myColumn->getType())?>"
                    data-orderable="<?=$myColumn->getIsOrderable() ? 'true' : 'false'?>"
                    data-searchable="<?=$myColumn->getIsSearchable() ? 'true' : 'false'?>"
                    data-class-name="<?= implode(' ', $myColumn->getCellClasses())?>"
                    data-title="<?=$this->e($myColumn->getTitle())?>"
                    data-name="<?=$this->e($myColumn->getName())?>">
                    <?=$this->e($myColumn->getTitle())?>
                </th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <?php foreach($myTable->getColumns() as $myColumn): ?>
                <th class="">
                    <?php if($myColumn->getIsSearchable()): ?>
                        <input class="col-search col-xs-12" type="text" placeholder="Search"/>
                    <?php endif; ?>
                </th>
            <?php endforeach; ?>
        </tr>
    </tfoot>
</table>