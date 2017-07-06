<?php
    use League\Plates\Template\Template;
    use Moose\Entity\User;
    use Moose\PlatesExtension\PlatesMooseExtension;
    use Moose\Util\CmnCnst;
    use Moose\ViewModel\DataTable;
    use Moose\ViewModel\DataTableColumn;
    use Moose\ViewModel\DataTableColumnInterface;
    use Moose\ViewModel\Paginable;
    use Moose\ViewModel\SectionBasic;
    /* @var $this Template|PlatesMooseExtension */
    /* @var $userPaginable Paginable */
    /* @var $user User */
    $this->layout('portal', ['title' => 'Posts']);
    $this->setActiveSection(SectionBasic::$USERLIST);
?>

<?php $this->insert('partials/component/tc_breadcrumb_sec') ?>

<!-- List of users -->
<div class="wrapper-userlist jscroll-body counter-main">
    <h1><?=$this->egettext('userlist-caption')?></h1>
    <?php
        $options = [];
        foreach ($tutorialGroups??[] as $tutGroup) {
            $options[$tutGroup->getId()] = (string)$tutGroup;
        }
        \asort($options, SORT_STRING);
        $this->insert('partials/form/datatable', [
            'table' => DataTable::builder('userlist_table')
            ->setRelativeUrl(CmnCnst::SERVLET_USER)
            ->setIsSearchable(true)
            ->setSearchDelay(1000)
            ->setRowClickHandler('toogleChildColumn')
            ->addColumn(DataTableColumn::builder('avatar')->title('userlist.head.avatar')->high(0)->image())
            ->addColumn(DataTableColumn::builder('regDate')->title('userlist.head.membersince')->order()->date())
            ->addColumn(DataTableColumn::builder('studentId')->title('userlist.head.studentid')->low(0)->order()->search())
            ->addColumn(DataTableColumn::builder('firstName')->title('userlist.head.firstname')->high(1)->order()->search())
            ->addColumn(DataTableColumn::builder('lastName')->title('userlist.head.lastname')->high(1)->order()->search())
            ->addColumn(DataTableColumn::builder('tutorialGroup')->title('userlist.head.tutgroup')->low(0)->badge()->setSearchTemplate(DataTableColumnInterface::SEARCH_DROPDOWN, [
                'isI18n' => false,
                'options' => $options
            ]))
            ->addColumn(DataTableColumn::builder('id')->title('userlist.head.id')->hide()->low(999)->text())
        ]);
    ?>
</div>
