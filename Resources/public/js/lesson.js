
function initTooltip(){
    $('a').tooltip();
}

function initSortable(){
    $( document ).ready(function() {
        $('.jquery-sortable-list').sortable({
            handle: 'i.icon-move',
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
    });
}
