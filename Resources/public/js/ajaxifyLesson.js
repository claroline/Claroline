
function resetTiny(){
    $('.tinymce').each(function(){
        $(this).tinymce().remove()
    });
}

function injectForm(obj, hashname){
    var newLink = $(obj);
    newLink.attr("data-path", newLink.attr('href'));
    newLink.attr('href', hashname+'-'+newLink.attr('data-chapter'));
    newLink.on('click', function (event){
        event.preventDefault();
        $.get(newLink.attr("data-path"))
            .done(function (data) {
                resetTiny();
                $('#chapter_content').html(data);
                initSelectedChapterListener();
                selectActiveChapter();
            })
        ;
    });
}


/*function injectFormForMove(obj, hashname){
    var newLink = $(obj);
    newLink.attr("data-path", newLink.attr('href'));
    newLink.attr('href', hashname+'-'+newLink.attr('data-chapter'));
    newLink.on('click', function (event){
        event.preventDefault();
        $.get(newLink.attr("data-path"))
            .done(function (data) {
                resetTiny();
                $('#chapter_content').html(data);
            })
        ;
    });
}*/

function popupForm(obj, hashname){
    var newLink = $(obj);
    newLink.attr("data-path", newLink.attr('href'));
    newLink.attr('href', hashname+'-'+newLink.attr('data-chapter')).attr('data-toggle', 'modal');
    var modalForm = modalForm = $('#deleteChapterPopup');
    newLink.on('click', function (event){
        event.preventDefault();
        $.get(newLink.attr("data-path"))
            .always(function () {
                if (modalForm !== null) {
                    modalForm.remove();
                }
            })
            .done(function (data) {
                $('body').append(data);
                modalForm = $('#deleteChapterPopup');
                modalForm.modal('show');
            })
        ;

    });
}

function selectActiveChapter(){
    var selectedId = $('#icap_lesson_chaptertype_parentChapter').val();
    if(selectedId != null && selectedId != undefined){
        resetActiveChapter();
        $('#menu_item_'+selectedId).addClass('active_chapter');
    }
}

function resetActiveChapter(){
    $('#lesson_menu').find('.active_chapter').each(function(){
        $(this).removeClass('active_chapter');
    });
}

function initSelectedChapterListener(){
    $('#icap_lesson_chaptertype_parentChapter').on('change', function (event){
        selectActiveChapter();
    });
}

$(document).ready(function() {
    'use strict';
    //form ajax insertion for chapter edition
    $('a.editchapter').each(function(){
        injectForm($(this), '#editChapter');
    });
    //form ajax insertion for chapter creation
    $('.createchapter').each(function(){
        injectForm($(this), '#createChapter');
    });
    //form ajax insertion for chapter move
    $('a.movechapter').each(function(){
        injectForm($(this), '#moveChapter');
    });
    //ajax popup for chapter delete form
    $('a.deletechapter').each(function(){
        popupForm($(this), '#deleteChapter');
    });
    //ajax popup for chapter delete form
    $('a.duplicatechapter').each(function(){
        injectForm($(this), '#duplicateChapter');
    });
});