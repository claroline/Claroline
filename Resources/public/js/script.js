$(document).ready(function() {
    'use strict';

    //Activate new section modal
    $('a.new-section').each(function(){
        var newLink = $(this);
        newLink.attr("data-path", newLink.attr('href'));
        newLink.attr('href', '#newSectionModal-'+newLink.attr('data-section')).attr('data-toggle', 'modal');
        var modalNewForm = null;
        newLink.on('click', function (event){
            if(modalNewForm === null){
                event.preventDefault();
                $.get(newLink.attr("data-path"))
                    .always(function () {
                        if (modalNewForm !== null) {
                            modalNewForm.remove();
                        }
                    })
                    .done(function (data) {
                        $('body').append(data);
                        modalNewForm = $('#newSectionModal-'+newLink.attr('data-section'));
                        console.log(modalNewForm.html());
                        modalNewForm.modal('show');
                    })
                ;
            }
        });
    });

    //Activate delete section modal
    $('a.delete-section').each(function(){
        var newLink = $(this);
        newLink.attr("data-path", newLink.attr('href'));
        newLink.attr('href', '#deleteSectionModal-'+newLink.attr('data-section')).attr('data-toggle', 'modal');
        var modalDeleteForm = null;
        newLink.on('click', function (event){
            if(modalDeleteForm === null){
                event.preventDefault();
                $.get(newLink.attr("data-path"))
                    .always(function () {
                        if (modalDeleteForm !== null) {
                            modalDeleteForm.remove();
                        }
                    })
                    .done(function (data) {
                        $('body').append(data);
                        modalDeleteForm = $('#deleteSectionModal-'+newLink.attr('data-section'));
                        console.log(modalDeleteForm.html());
                        modalDeleteForm.modal('show');
                    })
                ;
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