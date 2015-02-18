/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function importQTI(url) {
    $.ajax({
            type: "POST",
            url: url,
            cache: false,
            success: function (data) {
                displayImportForm(data);
            }
        });
}

function displayImportForm(data) {
    $('body').append(data);
}