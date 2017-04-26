<?php

use League\Plates\Template\Template;
use Moose\PlatesExtension\PlatesMooseExtension;
use Moose\Util\CmnCnst;
use Moose\ViewModel\ButtonFactory;
    /* @var $this Template|PlatesMooseExtension */
?>
<div id="footer">
    <div id="footer_left" class="col-md-4">
        <div class="bold"><?=$this->egettext('footer.ourvision')?></div>
        <div><?=$this->egettext('footer.ourvision.details')?></div>
    </div>
    <div id="footer_center" class="col-md-4">
        <div class="bold"><?=$this->egettext('footer.contact.header')?></div>
        <div id="before_button_distance"><?=$this->egettext('footer.contact')?></div>
        <form action="<?=$this->egetResource(CmnCnst::PATH_CONTACT)?>" method="get">            
            <?=$this->insert('partials/component/tc_action_button', [
                'button' => ButtonFactory::makeSubmitButton()
                    ->setLabelI18n('footer.contactus')
                    ->addHtmlAttribute('type', 'submit')
            ])?>
        </form>
    </div>
    <div id="footer_right" class="col-md-4">
        <div class="bold"><?=$this->egettext('footer.further.information')?></div>
            <ul>
                <li><a href="<?=$this->egetResource(CmnCnst::PATH_LEGALESE)?>"><?=$this->egettext('footer.disclaimer')?></a></li>
                <li><a href="<?=$this->egetResource(CmnCnst::PATH_LEGALESE)?>"><?=$this->egettext('footer.privacy')?></a></li>
                <li><a href="<?=$this->egetResource(CmnCnst::PATH_LEGALESE)?>"><?=$this->egettext('footer.terms')?></a></li>
            </ul>
       
    </div>
</div>

