<?php
    use League\Plates\Template\Template;
    use Moose\PlatesExtension\PlatesMooseExtension;
    use Moose\Util\CmnCnst;
    use Moose\ViewModel\DataTable;
    use Moose\ViewModel\DataTableColumn;
    use Moose\ViewModel\SectionBasic;
    /* @var $this Template|PlatesMooseExtension */
    $this->layout('portal', ['title' => 'Moose Module Manager']);
    $this->setActiveSection(SectionBasic::$SITE_SETTINGS_MAIL);
?>
<!-- Universities -->
<div class="wrapper-userlist jscroll-body counter-main">
    <h1><?=$this->egettext('m3-heading')?></h1>
    
    <?php
        $this->insert('partials/form/datatable', [
            'table' => DataTable::builder('university_table')
                ->setCaption('m3-university-caption')
                ->setRelativeUrl(CmnCnst::SERVLET_UNIVERSITY)
                ->setIsSearchable(true)
                ->setSearchDelay(1000)
                ->setRowClickHandler('toogleChildColumn')
                ->addColumn(DataTableColumn::builder('id')->title('m3.university.head.id')->hide()->low(999)->text())
                ->addColumn(DataTableColumn::builder('identifier')->title('m3.university.head.identifier')->high()->search())
                ->addColumn(DataTableColumn::builder('name')->title('m3.university.head.name')->medium()->order()->search())
        ]);
    ?>
    
    <?php
        $this->insert('partials/form/datatable', [
            'table' => DataTable::builder('fieldofstudy_table')
                ->setCaption('m3-fos-caption')
                ->setRelativeUrl(CmnCnst::SERVLET_FIELD_OF_STUDY)
                ->setIsSearchable(true)
                ->setSearchDelay(1000)
                ->setRowClickHandler('toogleChildColumn')
                ->addColumn(DataTableColumn::builder('id')->title('m3.fos.head.id')->hide()->low(999)->text())
                ->addColumn(DataTableColumn::builder('shortName')->title('m3.fos.head.identifier')->high()->order()->search())
                ->addColumn(DataTableColumn::builder('discipline')->title('m3.fos.head.name')->medium()->order()->search())
                ->addColumn(DataTableColumn::builder('subDiscipline')->title('m3.fos.head.name')->low()->order()->search())
        ]);
    ?>
    
    <?php
        $this->insert('partials/form/datatable', [
            'table' => DataTable::builder('course_table')
                ->setCaption('m3-course-caption')
                ->setRelativeUrl(CmnCnst::SERVLET_COURSE)
                ->setIsSearchable(true)
                ->setSearchDelay(1000)
                ->setRowClickHandler('toogleChildColumn')
                ->addColumn(DataTableColumn::builder('id')->title('m3.fos.head.id')->hide()->low(999)->text())
                ->addColumn(DataTableColumn::builder('name')->title('m3.fos.head.name')->high()->order()->search())
                ->addColumn(DataTableColumn::builder('credits')->title('m3.fos.head.credits')->medium()->order()->search())
                ->addColumn(DataTableColumn::builder('description')->title('m3.fos.head.description')->low()->order()->search())
        ]);
    ?>
</div>
