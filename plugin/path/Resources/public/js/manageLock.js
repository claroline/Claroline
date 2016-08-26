(function () {
    'use strict';

    $('.lockmsg').css('display', 'none');
    $(".unlock").on("click", function(){
         var that = $(this);
        $.ajax({
            url: Routing.generate(
                'innova_path_unlock_step',
                { step: $(this).attr("data-step"), user: $(this).attr("data-user")}
            ),
            type: 'GET',
            success: function (data) {
                var user = $(that).closest(".userstepunlock").find(".username").html();
                var steprow = that.parent().parent();
                var step = steprow.find('.stepname').html();
                //update the column data
                steprow.find('.stepstatus').html(window.Translator.trans('unseen', {}, 'path_wizards'));
                //set msg
                $('#unlocked').html(window.Translator.trans('step_unlocked', {'user':user, 'step':step}, 'path_wizards'));
                //display msg
                $('.lockmsg').css('display', 'block');
                //remove button
                $(that).remove();
            },
            error: function( jqXHR, textStatus, errorThrown){
                $('#unlocked').html("unable to unlock");
            }
        });
    });
}());
