<?php
    use League\Plates\Template\Template;
    use Moose\PlatesExtension\PlatesMooseExtension;
    use Moose\Util\CmnCnst;
    use Moose\ViewModel\BaseButton;
    use Moose\ViewModel\ButtonFactory;
    use Moose\ViewModel\SectionBasic;
    /* @var $this Template|PlatesMooseExtension */    
    $this->layout('portal');
    $this->setActiveSection(SectionBasic::$FILETREE);
?>

<?php $this->insert('partials/component/tc_dialog', [
    'id' => 'dialog_delete_document',
    'title' => 'filetree.rmfile.title',
    'body' => $this->gettext('filetree.rmfile.body'),
    'buttons' => [
        ButtonFactory::makeDeleteDocumentButton()
            ->addHtmlClass('btn-delete-document')
            ->setLabelI18n('filetree.rmfile.confirm')
            ->build(),
        ButtonFactory::makeCloseDialog()
            ->addHtmlClass('btn-dialog-close')
            ->setLabelI18n('filetree.rmfile.cancel')
            ->setType(BaseButton::TYPE_SUCCESS)
            ->build()
    ]
])?>

<h1><?=$this->egettext('filetree.heading')?></h1>

<div id="moose_file_manager" class="row file-manager" style="display: none;">
    <div class="col-md-3">
        <div
             class="filetree filetree-hierarchy "
             id="moose_filetree"
             data-root-title="<?=$this->egettext('filetree.root.node')?>"
             >
            <ul id="treeData" style="display: none;">
            </ul>
        </div>
    </div>

    <div class="col-md-9">
        <div class="filetree-details">
            <h2 style="display:none;" class="f-heading f-dir"><?=$this->egettext('filetree.directory')?>: <span class="f-documentTitle"></span></h2>
            <h2 style="display:none;" class="f-heading f-doc"><?=$this->egettext('filetree.document')?>: <span class="f-documentTitle"></span></h2>

            <a href target="_blank" class="f-preview f-doc" style="display:none;">
               <img src alt class="img-fluid">
            </a>

            <div class="dropzone-container f-dir" style="display:none;">
                <form
                      action="<?=$this->egetResource(CmnCnst::SERVLET_DOCUMENT)?>?action=single&did="
                      class="filetree-dropzone"
                >
                    <span class="glyphicon glyphicon-upload filetree-upload-button" aria-hidden="true"></span>
                    <div><?=$this->egettext('filetree.upload.new.hint')?></div>
                </form>
            </div>
            
            <?php $this->insert('partials/component/tc_action_button', [
                'button' => ButtonFactory::makeDownloadDocumentButton()
                    ->setLabelI18n('filetree.download')
                    ->addHtmlClass('btn-block btn-download-document f-doc')
                    ->hide()
            ])?>
            
            <?php $this->insert('partials/component/tc_action_button', [
                'button' => ButtonFactory::makeUpdateDocumentButton()
                    ->setLabelI18n('filetree.update')
                    ->addHtmlClass('btn-block btn-update-document f-doc')
                    ->hide()
            ])?>
            
            <?php $this->insert('partials/component/tc_action_button', [
                'button' => ButtonFactory::makeAddDirectoryButton()
                    ->setLabelI18n('filetree.add.dir')
                    ->addHtmlClass('btn-block btn-add-directory f-dir')
                    ->hide()
            ])?>

            <h3><?=$this->egettext('filetree.details')?></h3>
            <table class="table table-striped table-hover">
                <tr>
                    <td><?=$this->egettext('filetree.title')?></td>
                    <td class="">
                        <a href="#"
                            title="<?=$this->egettext('filetree.title.change')?>"
                            class="f-documentTitle f-doc-id editable editable-click"
                            data-type="text"
                            data-placeholder="<?=$this->egettext('filetree.title.change.placeholder')?>"
                            data-id="-1"
                            data-save-url="<?=$this->egetResource(CmnCnst::SERVLET_DOCUMENT)?>"
                            data-method="PATCH"
                            data-field="documentTitle"
                            data-action="meta"
                            data-emptytext="<?=$this->egettext('filetree.title.change.unknown')?>"
                        >dummy
                        </a>
                    </td>
                </tr>
                <tr>
                    <td><?=$this->egettext('filetree.description')?></td>
                    <td class="">
                        <a href="#"
                            title="<?=$this->egettext('filetree.description.change')?>"
                            class="f-description f-doc-id editable editable-click"
                            data-type="textarea"
                            data-placeholder="<?=$this->egettext('filetree.description.change.placeholder')?>"
                            data-id="-1"
                            data-save-url="<?=$this->egetResource(CmnCnst::SERVLET_DOCUMENT)?>"
                            data-method="PATCH"
                            data-field="description"
                            data-action="meta"
                            data-emptytext="<?=$this->egettext('filetree.description.change.unknown')?>"
                        >dummy
                        </a>
                    </td>
                </tr>
                <tr>
                    <td><?=$this->egettext('filetree.filename')?></td>
                    <td class="f-fileName"></td>
                </tr>
                <tr>
                    <td><?=$this->egettext('filetree.create.time')?></td>
                    <td class="f-createTime"></td>
                </tr>
                <tr>
                    <td><?=$this->egettext('filetree.mime')?></td>
                    <td class="f-mime"></td>
                </tr>
                <tr>
                    <td><?=$this->egettext('filetree.size')?></td>
                    <td class="f-size"></td>
                </tr>
            </table>

            <?php $this->insert('partials/component/tc_action_button', [
                'button' => ButtonFactory::makeOpenDialog('dialog_delete_document', true)
                    ->setLabelI18n('filetree.delete')
                    ->setType(BaseButton::TYPE_DEFAULT)
                    ->addHtmlClass('btn-block btn-delete-dlg')
                    ->hide()
            ])?>
            
        </div>    
    </div>
</div>
