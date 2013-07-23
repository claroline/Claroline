function insertStyle() {
    //"use strict";

    $('#ujm_exobundle_interactionqcmtype_interaction').find('div').first().find('label').first().remove();

    //supprime colonne ordre
    $('#ujm_exobundle_interactionqcmtype_shuffle').live('click', function () {
        if ($(this).is(':checked')) {
            $('#newTable .ligne_choice').each(function (index) {
                $(this).contents('td:nth-child(6)').remove();
            });
            $('#newTable tr:first').contents('th:nth-child(5)').hide();
        } else {
            $('#newTable .ligne_choice').each(function (index) {
                $('#newTable tr:first').contents('th:nth-child(5)').show();
                $(this).contents('td:nth-child(5)')
                        .after('<td id="button_down_click" class="colonne_choice" style="width: 125px; " > \n\
                               <div> <button id="button_up_click" type="button" class="button_up" >Up</button>\n\
                               <button type="button" class="button_down" >Down</button> </div>  </td>');
                $('#newTable .button_down').css({
                    'display': 'block',
                    'color': 'red',
                    'float': 'right'
                });
                $('#newTable .button_up').css({
                    'display': 'block',
                    'color':  'red',
                    'float': 'left'
                });
                $(this).contents('td:nth-child(6)').css({'border': '1px solid #aaaaaa'});
            });
        }
    });


    //afficher type qcm
    $('#ujm_exobundle_interactionqcmtype_typeQCM').change(function () {
        var src = $(this).val();
        if (src == 2) {
            //changer les reponse attendues en radio button
            $('#newTable .ligne_choice').each(function (index) {
                $(this).contents('td:nth-child(5)').find('input').prop('type', 'radio');
                $(this).contents('td:nth-child(5)').find('input').removeAttr('id');
                $(this).contents('td:nth-child(5)').find('input').attr('id', 'reponse_attendue_radio');
            });
        } else {
            var ii = 0;
            //changer les reponse attendues en checkbox button
            $('#newTable .ligne_choice').each(function (index) {
                    $(this).contents('td:nth-child(5)').find('input').prop('type', 'checkbox');
                    //donner des name différents au inputs
                    $(this).contents('td:nth-child(5)').find('input').removeAttr('name');
                    $(this).contents('td:nth-child(5)').find('input')
                            .attr('name', 'ujm_exobundle_interactionqcmtype[choices][' + ii + '][rightResponse]');
                    $(this).contents('td:nth-child(5)').find('input').removeAttr('id');
                    $(this).contents('td:nth-child(5)').find('input')
                            .attr('id', 'ujm_exobundle_interactionqcmtype_choices_' + ii + '_rightResponse');
                    ii = ii + 1;
                });
        }
    });

    if($('#ujm_exobundle_interactionqcmtype_weightResponse').is(':checked')) {
        $('#ujm_exobundle_interactionqcmtype_weightResponse').attr('checked', true);
        $('#newTable .ligne_choice').each(function (index) {
            $(this).contents('td:nth-child(3)').find('input').removeAttr('disabled');
        });
        $('#ujm_exobundle_interactionqcmtype_scoreRightResponse').attr('disabled', 'disabled');
        $('#ujm_exobundle_interactionqcmtype_scoreFalseResponse').attr('disabled', 'disabled');
    }

    $('#ujm_exobundle_interactionqcmtype_weightResponse').live('click', function () {
        if ($(this).is(':checked')) {
            $('#newTable .ligne_choice').each(function (index) {
                $(this).contents('td:nth-child(3)').find('input').removeAttr('disabled');
            });
            $('#ujm_exobundle_interactionqcmtype_scoreRightResponse').attr('disabled', 'disabled');
            $('#ujm_exobundle_interactionqcmtype_scoreFalseResponse').attr('disabled', 'disabled');
        } else {
            $('#newTable .ligne_choice').each(function (index) {
               $(this).contents('td:nth-child(3)').find('input').attr('disabled', 'disabled');
            });
            $('#ujm_exobundle_interactionqcmtype_scoreRightResponse').removeAttr('disabled');
            $('#ujm_exobundle_interactionqcmtype_scoreFalseResponse').removeAttr('disabled');
        }
    });


    $('#reponse_attendue_radio').live('click', function () {
        if ($(this).is(':checked')) {
            $('#newTable .ligne_choice').each(function (index) {
                $(this).contents('td:nth-child(5)').find('input').removeAttr('checked');
            }
            );
            $(this).attr('checked', 'checked');
        }
    });

    // clique boutons down et up - déplacement des lignes du tableau
    $('.button_down').live('click', function () {
        var rowToMove = $(this).parents('tr.ligne_choice:first');
        var next = rowToMove.next('tr.ligne_choice');
        if (next.length === 1) {
            next.after(rowToMove);
        }

        var i = 0;
        $('#newTable .ligne_choice').each(function (index) {
            $(this).find('label:first').text(i);
            $(this).contents('td:nth-child(2)').find('input').attr('value', i + 1);
            i = i + 1;
        });
    });

    $('.button_up').live('click', function () {
        var rowToMove = $(this).parents('tr.ligne_choice:first');
        var prev = rowToMove.prev('tr.ligne_choice');
        if (prev.length === 1) {
            prev.before(rowToMove);
        }

        var i = 0;
        $('#newTable .ligne_choice').each(function (index) {
            $(this).find('label:first').text(i);
            $(this).contents('td:nth-child(2)').find('input').attr('value', i + 1);
            i = i + 1;
        });
    });
}

//css choice
function choiceCSS(sourceImageDel) {
    //"use strict";
    $('#ujm_exobundle_interactionqcmtype_choices').children('div').each(function (index) {
        if(!$(this).hasClass('form-collection')) {
            $('#newTable').append('<tr class="ligne_choice" >  </tr>');
            $('#newTable .ligne_choice:last').append($(this));
        }
    });

    $('#newTable .ligne_choice:last').append('<td class="colonne_choice" >  </td>');
    $('#newTable .colonne_choice:last').append($('#newTable .ligne_choice:last').children('div').children('label')
            .first());
    $('#newTable .ligne_choice:last').children('div').children('div').children('div').each(function (index) {
        $('#newTable .ligne_choice:last').append('<td class="colonne_choice" >  </td>');
        $('#newTable .colonne_choice:last').append($(this));
    });


    //ajout colonne ordre
    if ($('#ujm_exobundle_interactionqcmtype_shuffle').is(':checked') === false) {
        $('#newTable .ligne_choice:last').contents('td:nth-child(7)')
                .after('<td id="button_down_click" class="colonne_choice" style="width: 125px; " >\n\
                       <div> <button id="button_up_click" type="button" class="button_up" >Up</button>\n\
                       <button type="button" class="button_down" >Down</button> </div>  </td>');
        $('#newTable .button_down').css({
            'display': 'block',
            'color': 'red',
            'float': 'right'
        });
        $('#newTable .button_up').css({
            'display': 'block',
            'color': 'red',
            'float': 'left'
        });
    }


    $('#newTable .ligne_choice:last').find('div').first().remove();
    $('#newTable .ligne_choice:last').find('td').first().remove();

    // css th
    $('#newTable th').css({
        'background-color': '#eee'
    });


    //type qcm is chiked
    if ($('#ujm_exobundle_interactionqcmtype_typeQCM').val() === 2) {
        $('#newTable .ligne_choice:last').contents('td:nth-child(6)').find('input').prop('type', 'radio');
        $('#newTable .ligne_choice:last').contents('td:nth-child(6)').find('input').removeAttr('id');
        $('#newTable .ligne_choice:last').contents('td:nth-child(6)').find('input')
                .attr('id', 'reponse_attendue_radio');
    } else {
        $('#newTable .ligne_choice:last').contents('td:nth-child(6)').find('input').prop('type', 'checkbox');
    }

    //ajout de la derniere colonne pr l'ajout et la supression
    $('#newTable .ligne_choice:last').contents('td:last')
            .after('<td><a href="#" id="delete_choice"><img src="' + sourceImageDel + '" /></a> </td> ');

    // clique boutons supprimer lignes du tableau
    $('#delete_choice').live('click', function () {
        $(this).parents('tr.ligne_choice:first').remove();

        if ($(this).attr('href') === '#') {
            return false;
        }
    });


    //css td
    $('#newTable tr').contents('td').css({'border': '1px solid #aaaaaa'});

    //ajouter bouton édition avancée ds colonne réponse
    $('#newTable .ligne_choice:last').contents('td:nth-child(1)').children('div')
            .before('<button id="button_editionA" type="button" class="button_editionA" >édition avancée</button>');
    $('#newTable .button_editionA').css({
        'display': 'block',
        'color': 'red',
        'float': 'right'
    });


    //ajustement
    $('#newTable .ligne_choice:last').contents('td:nth-child(2)').hide();

    //Assign points by response is chiked

    if ($('#ujm_exobundle_interactionqcmtype_weightResponse').is(':checked')) {
        $('#newTable .ligne_choice:last').contents('td:nth-child(3)').find('input').removeAttr('disabled');
    } else {
        $('#newTable .ligne_choice:last').contents('td:nth-child(3)').find('input').attr('disabled', 'disabled');
    }

    //$('td:hidden').find('input').attr('value', '1');

    if($('#ujm_exobundle_interactionqcmtype_typeQCM').val() == 2) {
        $('#newTable .ligne_choice').each(function (index) {
            $(this).contents('td:nth-child(5)').find('input').prop('type', 'radio');
            $(this).contents('td:nth-child(5)').find('input').removeAttr('id');
            $(this).contents('td:nth-child(5)').find('input').attr('id', 'reponse_attendue_radio');
        });
    }

}

function choiceCSSEdit(sourceImageDel, nbResponses) {
    //"use strict";
    $('#ujm_exobundle_interactionqcmtype_choices').children().first().children('div').each(function (index) {
        $('#newTable').append('<tr class="ligne_choice" >  </tr>');
        $('#newTable .ligne_choice:last').append($(this));
    });

    $('#newTable .ligne_choice').each(function (index) {
        $(this).append('<td class="colonne_choice" >  </td>');
        $(this).children('td').first().append($(this).children('div').children('label').first());
    });

    $('#newTable .ligne_choice').each(function (index) {
        var row = $(this);
        row.children('div').children('div').children('div').each(function (index) {
            row.append('<td class="colonne_choice" >  </td>');
            row.children('td').last().append($(this));
        });
    });

    //remplacer les input des réponses par des div pr interpreter le html
    $('#newTable .ligne_choice').each(function (index) {
        var row = $(this);
        var text = row.contents('td:nth-child(3)').find('textarea').val();
        row.contents('td:nth-child(3)').find('textarea').hide();
        row.contents('td:nth-child(3)')
                .append('<br />\n\<div id="divReplaceTextarea" style="border:solid 1px red; width:200px; height:110px;\n\
                                  padding:5px; overflow:auto; "></div> ');
        row.contents('td:nth-child(3)').children('div').last().html(text);
    });


    //ajout colonne ordre
    if ($('#ujm_exobundle_interactionqcmtype_shuffle').is(':checked') === false) {
        $('#newTable .ligne_choice').each(function (index) {
            $(this).contents('td:nth-child(7)')
                    .after('<td id="button_down_click" class="colonne_choice" style="width: 125px; " >\n\
                           <div> <button id="button_up_click" type="button" class="button_up" >Up</button>\n\
                           <button type="button" class="button_down" >Down</button> </div>  </td>');
        });
        $('#newTable .button_down').css({
            'display': 'block',
            'color': 'red',
            'float': 'right'
        });
        $('#newTable .button_up').css({
            'display': 'block',
            'color': 'red',
            'float': 'left'
        });
    }

    $('#newTable .ligne_choice').each(function (index) {
        $(this).find('div').first().remove();
        $(this).find('td').first().remove();
    });


    // css th
    $('#newTable th').css({
        'background-color': '#eee'
    });

    //type qcm is chiked
    if ($('#ujm_exobundle_interactionqcmtype_typeQCM').val() == 2) {
        $('#newTable .ligne_choice').each(function (index) {
            $(this).contents('td:nth-child(5)').find('input').prop('type', 'radio');
            $(this).contents('td:nth-child(5)').find('input').prop('name', 'choice');
            $(this).contents('td:nth-child(5)').find('input').removeAttr('id');
            $(this).contents('td:nth-child(5)').find('input').attr('id', 'reponse_attendue_radio');
            $(this).contents('td:nth-child(5)').find('input').removeAttr('name');
            $(this).contents('td:nth-child(5)').find('input')
                    .attr('name', 'ujm_exobundle_interactionqcmtype[choices][' + index + '][rightResponse]');

        });
    } else {
        $('#newTable .ligne_choice').each(function (index) {
            $(this).contents('td:nth-child(5)').find('input').prop('type', 'checkbox');
        });
    }

    //ajout de la derniere colonne pr l'ajout et la supression
    $('#newTable .ligne_choice').each(function (index) {
        if (nbResponses == 0) {
            $(this).contents('td:last').after('<td><a href="#" id="delete_choice">\n\
                                              <img src="' + sourceImageDel + '" /></a> </td> ');
        } else {
            $(this).contents('td:last').after('<td><img src="' + sourceImageDel + '" /></td> ');
        }
    });

    // clique boutons supprimer lignes du tableau
    $('#delete_choice').live('click', function () {
        $(this).parents('tr.ligne_choice:first').remove();
        //changer lindex de la ligne
        var i = 0;
        $('#newTable .ligne_choice').each(function (index) {
            $(this).find('label:first').text(i);
            i = i + 1;
        });
        if ($(this).attr('href') === '#') {
            return false;
        }
    });


    //css td
    $('#newTable tr').contents('td').css({'border': '1px solid #aaaaaa'});

    //ajouter bouton édition avancée ds colonne réponse
    $('#newTable .ligne_choice').each(function (index) {
        $(this).contents('td:nth-child(1)').children('div')
                .before('<button id="button_editionA" type="button" class="button_editionA" >édition avancée</button>');
    });
    $('#newTable .button_editionA').css({
        'display': 'block',
        'color': 'red',
        'float': 'right'
    });

    $('#newTable .ligne_choice').each(function (index) {
        $(this).contents('td:nth-child(1)').children('button').first().remove();
    });


    //ajustement
    $('#newTable .ligne_choice').each(function (index) {
        $(this).contents('td:nth-child(2)').hide();
    });

    if (!$('#ujm_exobundle_interactionqcmtype_weightResponse').is(':checked')) {
         /*$("*[id$='_weight']").each(function (index) {

         });*/
        $('#newTable .ligne_choice').each(function (index) {
                $(this).contents('td:nth-child(3)').find('input').attr('disabled', 'disabled');
            });
    }
}

//check form
function check_form(nbrChoices, answerCoched, labelEmpty, pointAnswers, pointAnswer, inviteQuestion) {
    //"use strict";

    //vérifier qu'il y a au moins deux choix
    if (($('newTable .ligne_choice').length) < 2) {
        alert(nbrChoices);
        return false;
    } else {
        //vérifier qu'il y a des réponse attendue
        var nbr_rep_coched = 0;
        $('#newTable .ligne_choice').each(function (index) {
            if ($(this).contents('td:nth-child(6)').find('input').is(':checked')) {
                nbr_rep_coched = nbr_rep_coched + 1;
            }
        });
        if (nbr_rep_coched === 0) {
            alert(answerCoched);
            return false;
        } else {
            //vérifier les points des reponses
            if ($('#ujm_exobundle_interactionqcmtype_scoreRightResponse').val() === 0) {
                if ($('#ujm_exobundle_interactionqcmtype_weightResponse').is(':checked')) {
                    var nbr_point = 0;
                    $('#newTable .ligne_choice').each(function (index) {
                        nbr_point = nbr_point + $(this).contents('td:nth-child(4)').find('input').val();
                    });

                    if (nbr_point <= 0) {
                        alert(pointAnswers);
                        nbr_point = 0;
                        return false;
                    }
                } else {
                    alert(pointAnswer);
                    return false;
                }
            } else {
                //vérifier que l'invite de la question est rempli
                if ($('#ujm_exobundle_interactionqcmtype_interaction_invite').val() === 0) {
                    alert(inviteQuestion);
                    return false;
                } else {
                    var ii = 0;
                    $('#newTable .ligne_choice').each(function (index) {
                        $(this).contents('td:nth-child(3)').find('input').attr('value', ii + 1);
                        ii = ii + 1;
                    });
                    return true;
                }
            }
        }
    }
}


//add choices
function addFormChoice(multipleResponse, uniqueResponse, add, responseNumber, response, point, comment,
    expectedResponse, order, sourceImageAdd, source_image_del, ChoicepositionForce) {
    //"use strict";

    $('#ujm_exobundle_interactionqcmtype_typeQCM')
        .find('option')
        .remove()
        .end()
        .append('<option selected="selected" value="1">' + multipleResponse + '</option>\n\
                <option value="2">' + uniqueResponse + '</option>');

    $('#ujm_exobundle_interactionqcmtype_choices').before('<a href="#" id="add_choice"><img src="' + sourceImageAdd +
        '">' + add + '</a>');

    $('#ujm_exobundle_interactionqcmtype_choices').after('<table style="border: 1px solid black;" id="newTable"><tr> <th>'
        + response + '</th> <th>' + point + '</th> <th>' + comment + '</th> <th>' + expectedResponse + '</th> <th>' +
        order + '</th> <th>' + ChoicepositionForce + '</th> <th>------</th> </tr></table>');
    $('#add_choice').css({
        'display': 'block',
        'color': 'green',
        'float': 'right'
    });

    if ($('#newTable').children('.ligne_choice').length === 0) {
        addChoice(source_image_del);
        addChoice(source_image_del);
    }

    $('#add_choice').click(function () {
        addChoice(source_image_del);
        if ($(this).attr('href') === '#') {
            return false;
        }
    });
}

function addFormChoiceEdit(multipleResponse, uniqueResponse, add, responseNumber, response, point, comment,
    expectedResponse, order, sourceImageAdd, nbResponses, typeQCM, ChoicepositionForce, source_image_del) {
    //"use strict";

    $('#ujm_exobundle_interactionqcmtype_typeQCM')
        .find('option')
        .remove()
        .end()

    if (typeQCM == 1) {
        $('#ujm_exobundle_interactionqcmtype_typeQCM').append('<option selected="selected" value="1">'
          + multipleResponse + '</option><option value="2">' + uniqueResponse + '</option>')
    } else {
        $('#ujm_exobundle_interactionqcmtype_typeQCM').append('<option value="1">' + multipleResponse +
            '</option><option selected="selected" value="2">' + uniqueResponse + '</option>')
    }

    if (nbResponses == 0){
        $('#ujm_exobundle_interactionqcmtype_choices').before('<a href="#" id="add_choice"><img src="'
          + sourceImageAdd + '">' + add + '</a>');
    }


    $('#ujm_exobundle_interactionqcmtype_choices').after('<table style="border: 1px solid black;" id="newTable"><tr> \n\
        <th>' + response + '</th> <th>' + point + '</th> <th>' + comment + '</th> <th>' +
        expectedResponse + '</th> <th>' + order + '</th> <th>' + ChoicepositionForce + '</th> \n\
        <th>------</th> </tr></table>');
    $('#add_choice').css({
            'display':'block',
            'color': 'green',
            'float':'right'
            });

    $('#add_choice').click(function () {
        addChoice(source_image_del);
        if ($(this).attr('href') === '#') {
            return false;
        }
    });
}

function addChoice(sourceImageDel) {
    //"use strict";
    var $container = $('#ujm_exobundle_interactionqcmtype_choices');
    var i = 0;
    var y = 0;
    var index = 0;
    
    index = $('#newTable .ligne_choice').length;


    $('#newTable .ligne_choice').each(function (index) {
        if(i < parseInt($(this).contents('td:nth-child(2)').children('div').children('input').attr('value'))) {
            i = parseInt($(this).contents('td:nth-child(2)').children('div').children('input').attr('value'));
        }
        y = y + 1;
        $(this).contents('td:nth-child(2)').children('div').children('input').attr('value',y);
    });
    index = i + 1;
    while($('#ujm_exobundle_interactionqcmtype_choices_'+index+'_ordre').length > 0) {
        index = index + 1;
    }


    $container.append(
        $($container.attr('data-prototype').replace(/__name__/g, index))
    );
    $('#ujm_exobundle_interactionqcmtype_choices_' + index + '_weight').width(50);
    $('#ujm_exobundle_interactionqcmtype_choices_'+index+'_ordre').attr("value",y+1);
    
    choiceCSS(sourceImageDel);
}