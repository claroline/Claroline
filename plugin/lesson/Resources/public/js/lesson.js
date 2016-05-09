
function init(){
    $( document ).ready(function() {
        initTooltip();
        initCollapsor();
        //fixLeftMenuAffixedSize();

        $(window).scroll(function(){
            if ($(window).scrollTop() <= 100)
            {
                $(".lesson_tooltip.btn_up.btn-primary").addClass("hidden");
            }
            else
            {
                $(".lesson_tooltip.btn_up.btn-primary").removeClass("hidden");
            }
        })
    });
}

function fixLeftMenuAffixedSize(){
    $('[data-clampedwidth]').each(function () {
        var elem = $(this);
        var parentPanel = elem.data('clampedwidth');
        var resizeFn = function () {
            var sideBarNavWidth = $(parentPanel).width() - parseInt(elem.css('paddingLeft')) - parseInt(elem.css('paddingRight')) - parseInt(elem.css('marginLeft')) - parseInt(elem.css('marginRight')) - parseInt(elem.css('borderLeftWidth')) - parseInt(elem.css('borderRightWidth'));
            elem.css('width', sideBarNavWidth);
        };

        resizeFn();
        $(window).resize(resizeFn);
    });
}

function initTooltip(){
    $('.lesson_tooltip').tooltip();
}

function initSortable(){
    var oldContainer;
    $('.jquery-sortable-list').sortable({
        afterMove: function (placeholder, container) {
            if(oldContainer != container){
                if(oldContainer)
                    oldContainer.el.removeClass("active")
                container.el.addClass("active")

                oldContainer = container
            }
        },
        onDrop: function($item, container, _super) {
            var path = $item.data('path');
            var parentId = $item.parent().data('list');
            var $previous_element = $item.prev();
            var brother = false;
            var firstposition = true;
            if($previous_element != null && $previous_element != undefined && $previous_element.attr('id') != undefined){
                parentId = $previous_element.attr('id');
                brother = true;
                firstposition = false;
            }
            $('#icap_lesson_movechaptertype_choiceChapter').val(parentId);
            $('#icap_lesson_movechaptertype_brother').prop('checked', brother);
            $('#icap_lesson_movechaptertype_firstposition').val(firstposition);

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
            container.el.removeClass("active");
        }
    });
    //disabled by default
    $('.jquery-sortable-list').sortable("disable");
    //init switch button enable/disable drag'n'drop
    $("#enable_move").on("click", function  (e) {
        //alert($(this).data("status"));
        $('.jquery-sortable-list').sortable($(this).data("status"));
        $(this).toggleClass("active");
        $(this).data("status", $(this).data("status") == "disable" ? "enable" : "disable");
        $('.menu-item').each(function() {
            $(this).toggleClass("cursor_move");
        });
        $('.draggable-item').each(function() {
            $(this).toggleClass("draggable-list");
        });
    })
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
        $(this).addClass("hidden");
        $('#expand_all').removeClass("hidden");
    });

    $('#expand_all').click(function() {
        $('.collapsor').each(function() {
            expand($(this));
        });
        $(this).addClass("hidden");
        $('#collapse_all').removeClass("hidden");
    });
}

function toggleCollapsor(obj){
    var chapter_id = $(obj).data('collapsor');
    $('#list_'+chapter_id).toggle(300);
    $('#collapsor_icon_'+chapter_id).toggleClass('fa-caret-down');
    $('#collapsor_icon_'+chapter_id).toggleClass('fa-caret-right');
}

function collapse(obj){
    var chapter_id = $(obj).data('collapsor');
    $('#list_'+chapter_id).hide(300);
    $('#collapsor_icon_'+chapter_id).removeClass('fa-caret-down');
    $('#collapsor_icon_'+chapter_id).addClass('fa-caret-right');
}

function expand(obj){
    var chapter_id = $(obj).data('collapsor');
    $('#list_'+chapter_id).show(300);
    $('#collapsor_icon_'+chapter_id).addClass('fa-caret-down');
    $('#collapsor_icon_'+chapter_id).removeClass('fa-caret-right');
}


function callback_tinymce_init(){
    // script called on tinymce initialization ...
}
