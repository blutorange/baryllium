<?php
    use League\Plates\Template\Template;
    use Moose\PlatesExtension\PlatesMooseExtension;
    use Moose\ViewModel\DataTableBuilderInterface;
    use Moose\ViewModel\DataTableColumnInterface;
    use Moose\ViewModel\DataTableInterface;
    /* @var $myTable DataTableInterface */
    /* @var $myColumn DataTableColumnInterface */
    /* @var $this Template|PlatesMooseExtension */
    $myTable = $table instanceof DataTableBuilderInterface ? $table->build() : $table;
?>

<table id="<?=$myTable->getId()?>"
        data-row-click="<?=$myTable->getRowClickHandler()?>"
        data-search-delay="<?=$myTable->getSearchDelay()?>"
        data-order-initial="<?=$myTable->getInitialOrderColumnIndex()?>"
        data-order-initial-dir="<?=$myTable->getIsInitialOrderAscending() ? 'asc' : 'desc'?>"
        data-url="<?=$this->e($myTable->getUrl())?>"
        data-action="<?=$this->e($myTable->getAction())?>"
        data-ordering="<?=$myTable->getIsOrderable() ? 'true' : 'false'?>"
        data-searching="<?=$myTable->getIsSearchable() ? 'true' : 'false'?>"
        data-paging="<?=$myTable->getIsPaginable() ? 'true' : 'false'?>"
        class="table table-striped table-bordered table-hover moose-datatable"
        cellspacing="0" width="100%">
    <thead>
        <tr>
            <?php foreach($myTable->getColumns() as $myColumn): ?>
                <th
                    data-visible="<?=$myColumn->getIsVisible() ? 'true' : 'false'?>"
                    data-type="<?=$this->e($myColumn->getType())?>"
                    data-orderable="<?=$myColumn->getIsOrderable() ? 'true' : 'false'?>"
                    data-searchable="<?=$myColumn->getSearchTemplate() ? 'true' : 'false'?>"
                    data-class-name="<?= implode(' ', $myColumn->getCellClasses())?>"
                    data-title="<?=$this->e($myColumn->getTitle())?>"
                    data-name="<?=$this->e($myColumn->getName())?>"
                    data-responsive-priority="<?=$myColumn->getResponsivePriority()?>"
                >
                    <?=$this->e($myColumn->getTitle())?>
                </th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <?php foreach($myTable->getColumns() as $myColumn): ?>
                <th class="table-search-wrapper">
                    <?php if($myColumn->getSearchTemplate()) {
                        $this->insert($myColumn->getSearchTemplate(), $myColumn->getSearchTemplateData());
                    } ?>
                </th>
            <?php endforeach; ?>
        </tr>
    </tfoot>
</table>