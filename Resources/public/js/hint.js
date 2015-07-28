var containerH = $('div[id$="_interaction_hints"]'); // Div which contain the dataprototype
var tableHints = $('#tableHint'); // div which contain the hints array

function newHint(label, penalty, addHint, deleteHint) {

    $('#divHint').find('.form-collection-add').remove();

    var begin = true;

    // create the button to add a hint
   var add = $('<a href="#" id="add_hint" class="btn btn-primary"><i class="fa fa-plus"></i>&nbsp;'+addHint+'</a>');
 // var add = $('<a href="#" id="add_hint" class="btn btn-default" >'+addHint+'</a>');

    // Add the button after the table
    tableHints.append(add);

    // When click, add a new hint in the table
    add.click(function (e) {
        if (begin == true) {
            tableHints.append('<table id="newTable2" class="table table-striped table-condensed" ><thead><tr style="background-color: lightsteelblue;"><th class="classic">'+label+'</th><th class="classic">'+penalty+'</th><th class="classic">'+deleteHint+'</th></tr></thead><tbody></tbody></table>');

            begin = false;
        }
        $('#newTable2').find('tbody').append('<tr></tr>');
        addHints(containerH, deleteHint);
        e.preventDefault(); // prevent add # in the url
        return false;
    });
}

// QCM Edition
function newHintEdit(label, penalty, addHint, deleteHint) {
    // create the button to add a hint

    var add = $('<a href="#" id="add_hint" class="btn btn-primary"><i class="fa fa-plus"></i>&nbsp;'+addHint+'</a>');
    //var add = $('<a href="#" id="add_hint" class="btn btn-default" >'+addHint+'</a>');

    // Add the button after the table
    tableHints.append(add);

    tableHints.append('<table id="newTable2" class="table table-striped table-condensed" ><thead><tr style="background-color: lightsteelblue;"><th class="classic">'+label+'</th><th class="classic">'+penalty+'</th><th class="classic">'+deleteHint+'</th></tr></thead><tbody></tbody></table>');

    // When click, add a new hint in the table
    add.click(function (e) {
        $('#newTable2').find('tbody').append('<tr></tr>');
        addHints(containerH, deleteHint);
        e.preventDefault(); // prevent add # in the url
        return false;
    });

    // Get the form field to fill rows of the hints' table
    $('.form-collection-element').each(function () {

        // Add a row to the table
        $('#newTable2').find('tbody').append('<tr></tr>');

         $(this).find('.row').each(function () {

            // Add the field of type input
            if ($(this).find('input').length) {
                $('#newTable2').find('tr:last').append('<td class="classic"></td>');
                $('#newTable2').find('td:last').append($(this).find('input'));
            }

            // Add the field of type textarea
            if ($(this).find('textarea').length) {
                $('#newTable2').find('tr:last').append('<td class="classic"></td>');
                $('#newTable2').find('td:last').append($(this).find('textarea'));
            }

            // Add the form errors
            $('#hintError').append($(this).find('span'));
        });

        // Add the delete button
        $('#newTable2').find('tr:last').append('<td class="classic"></td>');
        addDelete($('#newTable2').find('td:last'), deleteHint);
    });

    // Remove the useless fields form
    containerH.remove();
    tableHints.next().remove();
}

// Add a hint
function addHints(container, deleteHint) {

    var uniqChoiceID = false;

    var index = $('#newTable2').find('tr:not(:first)').length;

    while (uniqChoiceID == false) {
        if ($("*[id$='_interaction_hints_" + index + "_value']").length) {
            index++;
        } else {
            uniqChoiceID = true;
        }
    }

     // change the "name" by the index and delete the symfony delete form button
    var contain = $(container.attr('data-prototype').replace(/__name__/g, index)
        .replace('<a class="btn btn-danger remove" href="#">Delete</a>', '')
    );

    // Add tne button to delete a hint
    addDelete(contain, deleteHint);

    // Add the modified dataprototype to the page
    container.append(contain);

    // Get the form field to fill rows of the hints' table
    container.find('.row').each(function () {
        if ($(this).find('input').length) {
            $('#newTable2').find('tr:last').append('<td class="classic"></td>');
            $('#newTable2').find('td:last').append($(this).find('input'));
        }

        // Add the field of type textarea
        if ($(this).find('textarea').length) {
            $('#newTable2').find('tr:last').append('<td class="classic"></td>');
            $('#newTable2').find('td:last').append($(this).find('textarea'));
        }
    });

    // Add the delete button
    $('#newTable2').find('tr:last').append('<td class="classic"></td>');
    $('#newTable2').find('td:last').append(contain.find('a.btn-danger'));

    // Remove the useless fileds form
    container.remove();
    tableHints.next().remove();
}
