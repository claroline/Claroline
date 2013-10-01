$('.form-collection-add').each(function () {
    $(this).hide();
});

//css hint
function hintCSS() {
    //"use strict";
    //creer une ligne
    $("*[id$='_interaction_hints']").children('div').each(function (index) {
            $('#newTable2').append('<tr class="ligne_choice2" >  </tr>');
            $('#newTable2 .ligne_choice2:last').append($(this));
        }
    );

    //deplacer tous les elementts du hint ds une ligne
    $('#newTable2 .ligne_choice2:last').append('<td class="colonne_choice2 classic" >  </td>');
    $('#newTable2 .colonne_choice2:last').append($('#newTable2 .ligne_choice2:last').children('div').children('label').first());
    $('#newTable2 .ligne_choice2:last').children('div').children('div').children('div').each(function () {
            $('#newTable2 .ligne_choice2:last').append('<td class="colonne_choice2 classic" >  </td>');
            $('#newTable2 .colonne_choice2:last').append($(this));
        }
    );


    $('#newTable2 .ligne_choice2:last').find('div').first().remove();

    // css th
    $('#newTable2 th').css({
            'background-color': 'lightsteelblue'
        }
    );


    //ajout de la derniere colonne pr l'ajout et la supression
    $('#newTable2 .ligne_choice2:last').contents('td:last').after('<td class="classic"><a href="#" id="delete_choice2">supprimer</a></td>');


    // clique boutons supprimer lignes du tableau
    $('#delete_choice2').live('click', function () {
        $(this).parents('tr.ligne_choice2:first').remove();
        //changer l'index de la ligne
        var i2 = 0;
        $('#newTable2 .ligne_choice2').each(function (index) {
            $(this).find('label:first').text(i2);
            i2 = i2 + 1;
        });
        if ($(this).attr('href') === '#') {
            return false;
        }
    });

    //css td
    $('#newTable2 tr').contents('td').css({'border': '1px solid #aaaaaa'});
}

//css hint
function hintCSSEdit() {
    //"use strict";
    $("*[id$='_interaction_hints']").after('<table style="border: 1px solid black;" id="newTable2"><tr> <th class="classic">Num indice</th> <th class="classic">Indice</th> <th class="classic">Pénalité</th> <th class="classic">------</th> </tr></table>');

    //creer une ligne
    $("*[id$='_interaction_hints']").children().first().children('div').each(function (index) {
            $('#newTable2').append('<tr class="ligne_choice2" >  </tr>');
            $('#newTable2 .ligne_choice2:last').append($(this));
        }
    );

    //deplacer tous les elements du hint ds une ligne

    $('#newTable2 .ligne_choice2').each(function (index) {
            $(this).append('<td class="colonne_choice2" >  </td>');
            $(this).children('td').first().append($(this).children('div').children('label').first());
        }
    );

    $('#newTable2 .ligne_choice2').each(function (index) {
        var row = $(this);
        row.children('div').children('div').children('div').each(function (index) {
                row.append('<td class="colonne_choice2" >  </td>');
                row.children('td').last().append($(this));
            }
        );
    }
    );


    $('#newTable2 .ligne_choice2').each(function (index) {
        $(this).find('div').first().remove();
    }
    );


    // css th
    $('#newTable2 th').css({
            'background-color': 'lightsteelblue'
        }
    );

    //ajout de la derniere colonne pr l'ajout et la supression
    $('#newTable2 .ligne_choice2').each(function (index) {
        $(this).contents('td:last').after('<td><a href="#" id="delete_choice2">supprimer</a> </td> ');
    }
    );

    // clique boutons supprimer lignes du tableau
    $('#delete_choice2').live('click', function () {
        $(this).parents('tr.ligne_choice2:first').remove();
        //changer l'index de la ligne
        var i2 = 0;
        $('#newTable2 .ligne_choice2').each(function (index) {
            $(this).find('label:first').text(i2);
            i2 = i2 + 1;
        });
        if ($(this).attr('href') === '#') {
            return false;
        }
    }
    );

    //css td
    $('#newTable2 tr').contents('td').css({'border': '1px solid #aaaaaa'});
}


function addFormHintEdit(add_h, source_image_add) {
    //"use strict";
    $("*[id$='_interaction_hints']").before('<a class="btn btn-primary" id="add_hint"><i class="icon-plus"></i>&nbsp;' + add_h + '</a>');
    $('#add_hint').click(function () {
        addHint();

        if ($(this).attr('href') === '#') {
            return false;
        }
    }
    );
}

function addFormHint(add_h, hint_number, hint, Penalty, source_image_add) {
    //"use strict";
    $("*[id$='_interaction_hints']").before('<a class="btn btn-primary" id="add_hint"><i class="icon-plus"></i>&nbsp;' + add_h + '</a>');
    $('#add_hint').click(function () {
        addHint(hint_number, hint, Penalty);
        if ($(this).attr('href') === '#') {
            return false;
        }
    }
    );
}

function addHint(hint_number, hint, Penalty) {
    //"use strict";

    var $container = $("*[id$='_interaction_hints']");
    if ($('#newTable2').length) {
        index2 = $('#newTable2 .ligne_choice2').length;
    } else {
        index2 = 0;
        if(typeof hint_number != 'undefined') {
            $("*[id$='_interaction_hints']").after('<table style="border: 1px solid black;" id="newTable2"><tr> <th class="classic">'
                + hint_number + '</th> <th class="classic">' + hint + '</th> <th class="classic">' + Penalty
                + '</th> <th class="classic">------</th> </tr></table>');
        }
    }

    $container.append(
        $($container.attr('data-prototype').replace(/__name__/g, index2))
    );
    hintCSS();
}
