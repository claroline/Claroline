function openCat(url) {
    $.ajax({
        type: "POST",
        url: url,
        cache: false,
        success: function (data) {
            markCat(data);
        }
    });
}

function markCat(data) {
    $('body').append(data);
}

$(document.body).on('hidden.bs.modal', function () {
    $('#modalcategory').remove();
    $('#editCategory').css({"display" : "inline-block"});
});
