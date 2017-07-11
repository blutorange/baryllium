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

<div id="moose_file_manager" class="row file-manager" style="display: none;" data-has-opal="<?=$permissions['opal']?>">
    <div class="col-md-4">
        <div
             class="filetree filetree-hierarchy "
             id="moose_filetree"
             data-root-title="<?=$this->egettext('filetree.root.node')?>"
             data-opal-title="<?=$this->egettext('filetree.opal.node')?>"
        >
            <ul id="treeData" style="display: none;">
            </ul>
        </div>
    </div>

    <div class="col-md-8">
        <div class="filetree-details">
            <h2 style="display:none;" class="f-heading f-dir f-notroot"><?=$this->egettext('filetree.directory')?>: <span class="f-documentTitle f-name"></span></h2>
            <h2 style="display:none;" class="f-heading f-doc f-notroot"><?=$this->egettext('filetree.document')?>: <span class="f-documentTitle f-name"></span></h2>

            <div class="f-internal f-root">
                <h2><?=$this->egettext('filetree.internal.header')?></h2>
                <?=$this->gettext('filetree.internal.sections.html')?>
            </div>
            
            <div class="f-opal f-root">
                <h2><?=$this->egettext('filetree.opal.header')?></h2>
                <?=$this->gettext('filetree.opal.sections.html')?>
            </div>
            
            <a href target="_blank" class="f-preview f-doc" style="display:none;">
               <img src alt class="img-fluid">
            </a>

            <div class="dropzone-container f-dir f-internal f-notroot" style="display:none;">
                <form
                      action="<?=$this->egetResource(CmnCnst::SERVLET_DOCUMENT)?>?action=single&did="
                      class="filetree-dropzone"
                >
                    <span class="glyphicon glyphicon-upload filetree-upload-button" aria-hidden="true"></span>
                    <div><?=$this->egettext('filetree.upload.new.hint')?></div>
                </form>
            </div>
            
            <?php $this->insert('partials/component/tc_action_button', [
                'button' => ButtonFactory::makeDownloadButton(CmnCnst::BUTTON_DOWNLOAD_DOCUMENT)
                    ->setLabelI18n('filetree.download')
                    ->addHtmlClass('btn-lg btn-block btn-download-document f-doc f-internal')
                    ->hide()
            ])?>

            <?php $this->insert('partials/component/tc_action_button', [
                'button' => ButtonFactory::makeDownloadButton(CmnCnst::BUTTON_DOWNLOAD_OPAL)
                    ->setLabelI18n('filetree.download')
                    ->addHtmlClass('btn-lg btn-block btn-download-opal f-doc f-opal')
                    ->hide()
            ])?>

            <?php $this->insert('partials/component/tc_action_button', [
                'button' => ButtonFactory::makeRefreshTreeButton('#moose_filetree')
                    ->setLabelI18n('filetree.opal.refresh')
                    ->addHtmlClass('btn-block f-opal f-notroot')
                    ->hide()
            ])?>
            
            <?php $this->insert('partials/component/tc_action_button', [
                'button' => ButtonFactory::makeUpdateDocumentButton()
                    ->setLabelI18n('filetree.update')
                    ->addHtmlClass('btn-block btn-update-document f-doc f-internal f-notroot')
                    ->hide()
            ])?>
            
            <?php $this->insert('partials/component/tc_action_button', [
                'button' => ButtonFactory::makeAddDirectoryButton()
                    ->setLabelI18n('filetree.add.dir')
                    ->addHtmlClass('btn-block btn-add-directory f-dir f-internal f-notroot')
                    ->hide()
            ])?>

            <h3 class="f-notroot"><?=$this->egettext('filetree.details')?></h3>
            <table class="table table-striped table-hover f-notroot">
                <tr class="f-internal">
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
                        >dummy</a>
                    </td>
                </tr>
                <tr class="f-opal">
                    <td><?=$this->egettext('filetree.title')?></td>
                    <td class="f-name" data-emptytext="<?=$this->egettext('filetree.filename.change.unknown')?>"></td>
                </tr>
                <tr class="f-internal">
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
                        >dummy</a>
                    </td>
                </tr>
                <tr class="f-opal">
                    <td><?=$this->egettext('filetree.description')?></td>
                    <td class="f-description" data-emptytext="<?=$this->egettext('filetree.description.change.unknown')?>"></td>
                </tr>
                <tr>
                    <td><?=$this->egettext('filetree.filename')?></td>
                    <td class="f-fileName" data-emptytext="<?=$this->egettext('filetree.filename.change.unknown')?>"></td>
                </tr>
                <tr>
                    <td><?=$this->egettext('filetree.create.time')?></td>
                    <td class="f-createTime f-modificationDate"></td>
                </tr>
                <tr>
                    <td><?=$this->egettext('filetree.mime')?></td>
                    <td class="f-mime f-mimeType"></td>
                </tr>
                <tr>
                    <td><?=$this->egettext('filetree.size')?></td>
                    <td class="f-size f-byteSize"></td>
                </tr>
            </table>

            <?php $this->insert('partials/component/tc_action_button', [
                'button' => ButtonFactory::makeOpenDialog('dialog_delete_document', true)
                    ->setLabelI18n('filetree.delete')
                    ->setType(BaseButton::TYPE_DEFAULT)
                    ->addHtmlClass('btn-block btn-delete-dlg f-internal')
                    ->hide()
            ])?>
        </div>    
    </div>
</div>
