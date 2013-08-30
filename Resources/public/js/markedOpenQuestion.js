function openMark(url) {
    $.ajax({
            type: "POST",
            url: url,
            cache: false,
            success: function (data) {
                markPop(data);
            }
        });
}

function markPop(data) {
    
    $('body').append(data);
    
}

$(document.body).on('hidden.bs.modal', function () { 
    $('#modalopenmark').remove();
});
