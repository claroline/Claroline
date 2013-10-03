var containerH = $('div#*[id$="_interaction_hints"]'); // Div which contain the dataprototype
var tableHints = $('#tableHint'); // div which contain the hints array
var index; // number of hints

function newHint(label, penalty, addHint, deleteHint) {

    $('.panel-body').find('a:contains("Add")').remove();

    var begin = true;

    index = 0;

    // create the button to add a hint
    var add = $('<a href="#" id="add_hint" class="btn btn-primary"><i class="icon-plus"></i>&nbsp;'+addHint+'</a>');

    // Add the button after the table
    tableHints.append(add);

    // When click, add a new hint in the table
    add.click(function (e) {
        if (begin == true) {
            tableHints.append('<table id="newTable2" class="table table-striped table-bordered table-condensed" style="width:500px;"><thead><tr style="background-color: lightsteelblue;"><th class="classic">'+label+'</th><th class="classic">'+penalty+'</th><th class="classic">-----</th></tr></thead><tbody></tbody></table>');
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
    var add = $('<a href="#" id="add_hint" class="btn btn-primary"><i class="icon-plus"></i>&nbsp;'+addHint+'</a><br/><br/>');

    // Add the button after the table
    tableHints.append(add);

    tableHints.append('<table id="newTable2" class="table table-striped table-bordered table-condensed" style="width:500px;"><thead><tr style="background-color: lightsteelblue;"><th class="classic">'+label+'</th><th class="classic">'+penalty+'</th><th class="classic">-----</th></tr></thead><tbody></tbody></table>');

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

    // Get the number of hints
    index = $('#newTable2').find('tr:not(:first)').length;
}

// Add a hint
function addHints(container, deleteHint) {
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
    $('#newTable2').find('td:last').append(container.find('a:contains("Supprimer")'));

    // Remove the useless fileds form
    container.remove();
    tableHints.next().remove();

    // Increase number of hints
    index++;
}

// Delete a hint
function addDelete(tr, deleteHint) {

    // Create the button to delete a hint
    var delLink = $('<a href="#" class="btn btn-danger">'+deleteHint+'</a>');

    // Add the button to the row
    tr.append(delLink);

    // When click, delete the matching hint's row in the table
    delLink.click(function(e) {
        $(this).parent('td').parent('tr').remove();
        e.preventDefault();
        return false;
    });
}