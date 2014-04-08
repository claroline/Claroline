$(document).ready(function() {
    'use strict';

    //Activate new section modal
    $('a.new-section').each(function(){
        var newLink = $(this);
        newLink.attr("data-path", newLink.attr('href'));
        newLink.attr('href', '#newSectionContainer-'+newLink.attr('data-section'));
        var containerNewForm = null;
        newLink.on('click', function (event){
            if(typeof newLink.attr("data-empty") === 'undefined'){
                event.preventDefault();
                $.get(newLink.attr("data-path"))
                    .always(function () {
                        if (containerNewForm !== null) {
                            containerNewForm.remove();
                        }
                    })
                    .done(function (data) {
                        $('#wnsc-'+newLink.attr('data-section')).html(data);
                        newLink.attr('data-empty','false');
                        containerNewForm = $('#newSectionContainer-'+newLink.attr('data-section'));
                        containerNewForm.find('#icap_wiki_section_type_activeContribution_text').attr('id', 'icap_wiki_section_type_'+newLink.attr('data-section'));
                        $('#wnsc-'+newLink.attr('data-section')).show();
                        $('#wesc-'+newLink.attr('data-section')).hide();
                        $('#wst-'+newLink.attr('data-section')).show();
                    })
                ;
            }
            else {
                $('#wnsc-'+newLink.attr('data-section')).show();
                $('#wesc-'+newLink.attr('data-section')).hide();
                $('#wst-'+newLink.attr('data-section')).show();
            }
        });
    });

    //Activate edit section modal
    $('a.edit-section').each(function(){
        var editLink = $(this);
        editLink.attr("data-path", editLink.attr('href'));
        editLink.attr('href', '#editSectionContainer-'+editLink.attr('data-section'));
        var containerNewForm = null;
        editLink.on('click', function (event){
            if(typeof editLink.attr("data-empty") === 'undefined'){
                event.preventDefault();
                $.get(editLink.attr("data-path"))
                    .always(function () {
                        if (containerNewForm !== null) {
                            containerNewForm.remove();
                        }
                    })
                    .done(function (data) {
                        $('#wesc-'+editLink.attr('data-section')).html(data);
                        editLink.attr('data-empty','false');
                        containerNewForm = $('#editSectionContainer-'+editLink.attr('data-section'));
                        containerNewForm.find('#icap_wiki_edit_section_type_activeContribution_text').attr('id', 'icap_wiki_edit_section_type_'+editLink.attr('data-section'));
                        $('#wesc-'+editLink.attr('data-section')).show();
                        $('#wst-'+editLink.attr('data-section')).hide();
                        $('#wnsc-'+editLink.attr('data-section')).hide();
                    })
                ;
            }
            else {
                $('#wesc-'+editLink.attr('data-section')).show();
                $('#wst-'+editLink.attr('data-section')).hide();
                $('#wnsc-'+editLink.attr('data-section')).hide();
            }
        });
    });

    //Activate drag-n-drop for section move
    var startReferenceSectionId = null;
    var startIsBrother = null;
    $('#wiki-contents-list').sortable({
        onDragStart: function (item, container, _super) {
            if (item.prev().length > 0) {
                startReferenceSectionId = item.prev().attr("data-section");
                startIsBrother = true;
            }
            else {
                startReferenceSectionId = item.parent().attr("data-section");
            }
            _super(item, container);
        },
        onDrop: function (item, container, _super) {
            var sectionId = item.attr("data-section");
            var referenceSectionId = null;
            var isBrother = false;
            if (item.prev().length > 0) {
                referenceSectionId = item.prev().attr("data-section");
                isBrother = true;
            }
            else {
                referenceSectionId = item.parent().attr("data-section");
            }
            var moveSectionRouteTmp = moveSectionRoute.replace("/0/0/true","/"+sectionId+"/"+referenceSectionId+"/"+isBrother);
            
            $.post(moveSectionRouteTmp)
                .success(function (data) {
                    window.location.reload();
                })
                .error(function () {
                    if (startIsBrother == true) {
                        $("#li-"+startReferenceSectionId).after($("#li-"+sectionId));
                    }
                    else {
                        if (startReferenceSectionId != 0) {
                            $("#li-"+startReferenceSectionId+" > ul").prepend($("#li-"+sectionId));
                        }
                        else {
                            $("ul#wiki-contents-list").prepend($("#li-"+sectionId));
                        }
                    }
                    startIsBrother = null;
                    startReferenceSectionId = null;
                });
            
            _super(item, container);
        }
    });
    $('#wiki-contents-list').sortable("disable");
    $("#move-contents-trigger").on("click", function(){
        if ($(this).hasClass("active")) {
            $(this).removeClass("active");
            $('#wiki-contents-list').sortable("disable");
            $('ul.sortable-list').removeClass("sortable-enable");
        }
        else {
            $(this).addClass("active");
            $('#wiki-contents-list').sortable("enable");
            $('ul.sortable-list').addClass("sortable-enable");
        }
    });

    
});