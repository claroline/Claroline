
function init(){
    $( document ).ready(function() {
        initTooltip();
        initCollapsor();
    });
}

function initTooltip(){
    $('.lesson_tooltip').tooltip();
}

function initSortable(){
    $('.jquery-sortable-list').sortable({
        handle: 'i.handle_sortable',
        onDrop: function($item, container, _super) {
            var path = $item.data('path');
            var parentId = $item.parent().attr('id');
            var $previous_element = $item.prev();
            var brother = false;
            if($previous_element != null && $previous_element != undefined && $previous_element.attr('id') != undefined){
                parentId = $previous_element.attr('id');
                brother = true;
            }
            $('#icap_lesson_movechaptertype_choiceChapter').val(parentId);
            $('#icap_lesson_movechaptertype_brother').prop('checked', brother);
            var request = $.post( path, $('#moveform').serialize())
                .done(function() {
                    // alert( "success" );
                    _super($item, container);
                })
                .fail(function() {
                    //alert( "error" );
                })
                .always(function() {
                    // alert( "complete" );
                });
        }
    });
}

function initCollapsor(){
    $('.collapsor').each(function() {
        $(this).click(function() {
            toggleCollapsor($(this));
        });
    });

    $('#collapse_all').click(function() {
        $('.collapsor').each(function() {
            collapse($(this));
        });
    });

    $('#expand_all').click(function() {
        $('.collapsor').each(function() {
            expand($(this));
        });
    });
}

function toggleCollapsor(obj){
    var chapter_id = $(obj).data('collapsor');
    $('#list_'+chapter_id).toggle(300);
    $('#collapsor_icon_'+chapter_id).toggleClass('icon-collapse');
    $('#collapsor_icon_'+chapter_id).toggleClass('icon-expand');
}

function collapse(obj){
    var chapter_id = $(obj).data('collapsor');
    $('#list_'+chapter_id).hide(300);
    $('#collapsor_icon_'+chapter_id).removeClass('icon-collapse');
    $('#collapsor_icon_'+chapter_id).addClass('icon-expand');
}

function expand(obj){
    var chapter_id = $(obj).data('collapsor');
    $('#list_'+chapter_id).show(300);
    $('#collapsor_icon_'+chapter_id).addClass('icon-collapse');
    $('#collapsor_icon_'+chapter_id).removeClass('icon-expand');
}

function checkMoveValue(){
/*    $('#move_form_nojs').each(function() {
        alert("passe");
    });*/

    //alert($sel.attr('id'));
/*    $sel.change(function() {
        alert("changed");
        if(this.selectedIndex != 0){
            $('#icap_lesson_movechaptertype_brother').prop("disabled", false);
        }else{
            $('#icap_lesson_movechaptertype_brother').prop("checked", false);
            $('#icap_lesson_movechaptertype_brother').prop("disabled", true);
        }
    });*/
}
