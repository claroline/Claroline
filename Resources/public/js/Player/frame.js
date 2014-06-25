$( document ).ready(function() {
    $('.resource-frame').on('load', function () {
        resizeIframe($(this));
    });

    $(document).resize(function () {
        resizeIframe($('.resource-frame'));
    })
});

function resizeIframe(frame) {
    frame.animate({ height: frame.contents().find("#wrap > .container").height() + 25}, 100, function() {});
}