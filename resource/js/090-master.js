/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$('document').ready(function () {
    $('body').css('opacity', 0);
    $('body').animate({opacity: '1'}, "slow");
    
    window.parsley.setLocale(window.moose.locale);
    $('[data-bootstrap-parsley]').parsley({
        successClass: 'has-success',
        errorClass: 'has-error',
        classHandler: function(field) {
            return field.$element.closest('.form-group');
        },
        errorsWrapper: '<ul class=\"help-block\"></ul>',
        errorElem: '<li></li>'
    });

    $('[data-provide="markdown-loc"]').each(function(){
        //console.log(e.parseContent());    
        var input = $(document.getElementById(this.id + "-hidden"));
        $(this).markdown({
            language: window.moose.locale,
            onBlur: function(e) {
                input.val(e.parseContent());
            }
        });
    });
});