// Make the questions' exercise sortable
$(document).ready(function (){
    $('tbody').sortable({
        update: function (event, ui) {
            $('#SaveOrder').css({"display" : "inline-block"}); // Display button to save the new order
        }
    });
});

// Save the new order of the questions' exercise
function SaveNewOrder(path, exoID, currentPage, questionMaxPerPage) {

    var order = new Array();

    // Get the new order
    $('#QuestionArray tr').each(function () {
        if ($(this).find('td').eq(5).html() != null) {
            order.push($(this).find('td').eq(5).html().trim());
        }
    });

    $.ajax({
        type: 'POST',
        url: path,
        data: {
            exoID: exoID,
            order: order,
            currentPage: currentPage,
            questionMaxPerPage: questionMaxPerPage
        }
    });

    $('#SaveOrder').css({"display" : "none"}); // Hide to button
}
