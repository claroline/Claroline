/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function importQTI(url, exoID) {
    $.ajax({
            type: "POST",
            url: url,
            cache: false,
            data: {
                exoID : exoID,
            },
            success: function (data) {
                displayImportForm(data);
            }
        });
}

function displayImportForm(data) {
    $('body').append(data);
}