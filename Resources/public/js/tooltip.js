/**
 * Created by Aurelien on 26/05/14.
 */
$(document).ready(function () {

    /**
     Allow to use tooltip in disable button inside a button group
     creating a div in absolute pos
     **/
    $('input:disabled, button:disabled, a.disabled').after(function (e) {
        var d = $("<div >");
        var i = $(this);
        var offset = i.offset();
        var left_pos = offset.left - $(this).parent().offset().left; // due  to table and groupButton
        d.css({
            height: i.outerHeight(),
            width: i.outerWidth(),
            position: "absolute",
            left: left_pos
        });
        d.attr("title", i.attr("title"));
        d.attr('data-placement', i.attr('data-placement'));
        d.attr('data-container', 'body'); // specific to group Buttons.
        d.tooltip();
        return d;
    });
});