$("a").remove(".form-collection-add");

// div which contain the choices array
var tableHoles = $('#tableHoles'); // div which contain the choices array

// Div which contain the dataprototype
var container = $('div#ujm_exobundle_interactionholetype_holes');

//To find the prototype of wordReponse which is integrated in the prototye of hole
//tableHoles.append('<div id="prototypes" style="display:none"></div>');
var containerWR = container.attr('data-prototype').valueOf();
$('#prototypes').append(containerWR);
containerWR = $('#ujm_exobundle_interactionholetype_holes___name___wordResponses');

var langKeyWord;
var langPoint;
var langDel;
var langComment;
var langEdition;

function addFormHole( response, size, orthography, del, selector, source_image_add, wlangKeyWord, wlangPoint,comment,edition) {
    langKeyWord = wlangKeyWord;
    langPoint   = wlangPoint;
    langDel     = '<i class="fa fa-close"></i>';
    langComment = comment;
    langEdition     = edition;

    tableHoles.append('<table id="newTable" class="table table-striped table-condensed"><thead id="Entete"><tr><th >' + size + '</th><th style="display:none;">' + orthography + '</th><th >' + selector + '</th><th >' + response + '</th><th >' + del + '</th></tr></thead><tbody></tbody></table>');
    $('tbody').sortable();

    $('#newTable').css({"display" : "none"});


}

function addFormHoleEdit(response, size, orthography, del, selector, source_image_add, wlangKeyWord, wlangPoint, nbResponses,comment,edition) {
    langKeyWord = wlangKeyWord;
    langPoint   = wlangPoint;
    langDel     = '<i class="fa fa-close"></i>';
    langComment = comment;
    langEdition     = edition;
    var index;
    var i = 0;

    if (nbResponses == 0) {
        tableHoles.append('<table id="newTable" class="table table-striped table-condensed"><thead><tr style="background-color: lightsteelblue;"><th class="classic">' + size + '</th><th class="classic" style="display:none;">' + orthography + '</th><th class="classic">' + selector + '</th><th class="classic">' + response + '</th><th class="classic">' + del + '</th></tr></thead><tbody class="bodyHole"></tbody></table>');
    } else {
        tableHoles.append('<table id="newTable" class="table table-striped table-condensed"><thead><tr style="background-color: lightsteelblue;"><th class="classic">' + size + '</th><th class="classic" style="display:none;">' + orthography + '</th><th class="classic">' + selector + '</th><th class="classic">' + response + '</th></tr></thead><tbody class="bodyHole"></tbody></table>');
    }
    $('tbody').sortable();

    container.children().first().children('div').each(function () {

        var nbHole = $('#newTable').find('.trHole').length;

        // Add a row to the table
        $('#newTable').find('.bodyHole').append('<tr class="trHole"></tr>');

         $(this).find('.row').each(function () {

            if ($(this).find('input').length) {
                //not yet implemented so don't create
             if ($(this).find('input').attr("id").indexOf('orthography') == -1) {
                $('#newTable').find('tr:last').append('<td class="classic"></td>');
                $('#newTable').find('td:last').append($(this).find('input'));
            } else {
                $('#newTable').find('tr:last').append('<td class="classic" style="display:none;"></td>');
                $('#newTable').find('td:last').append($(this).find('input'));
            }
            }

        });

        $('#newTable').find('tr:last').find('td:first').css('display', 'none');
        index = $('#newTable').find('tr:last').find('td:first').find('input:first').val();

        //******For Key words********
        var addwr = '';
        if (nbResponses == 0) {
            addwr = '<a href="#" id="add_keyword_' + index + '" class="btn btn-primary"><i class="fa fa-plus"></i>&nbsp;' + langKeyWord + '</a>';
        }

        $('#newTable').find('.trHole:last').find('td:last')
            .append('<table id="tabWR_' + index + '" class="table"><tbody></tbody></table>' + addwr);

        $('#add_keyword_' + index).click(function (e) {
            //var ind = $(this).parents(".trHole").index();
            var ind = $(this).parents(".trHole").find('td:first').find('input:first').attr('id').valueOf();
            ind = ind.replace('ujm_exobundle_interactionholetype_holes_', '');
            ind = ind.replace('_position', '');
            var idTabWR = $(this).attr('id');
            idTabWR = idTabWR.replace('add_keyword_', '');

            addWR(ind, idTabWR);
            e.preventDefault(); // prevent add # in the url
            return false;
        });

        $('#newTable').find('.trHole:last').find('td:last').find('input').each(function () {
            //i = nb input found, 3 input per row (response, point, caseSensitive)
            if (i == 0) {
                $('#tabWR_'+index).find('tbody').append('<tr class="trWR"></tr>');
            } else if (i > 2) {
                i = 0;
                $('#tabWR_'+index).find('tbody').append('<tr class="trWR"></tr>');
            }

            $('#tabWR_' + index).find('tr:last').append('<td class="classic"></td>');
            $(this).appendTo($('#tabWR_' + index).find('tr:last').find('td:last'));
            i++;
            
            //add buton delete for a key word
            if ( (nbResponses == 0) && (i > 2) && ($('#tabWR_' + index).find('.trWR').length > 1)) {
                $('#tabWR_' + index).find('tr:last').append('<td class="classic"></td>');
                $('#tabWR_' + index).find('td:last').append(
                    '<a id="wr_' + index + '_' + $('#tabWR_' + index).find('.trWR').length + '" href="#" class="btn btn-default"><i style="color : red" class="fa fa-trash-o"></i></a>'
                );
                
                // When click, delete the matching keyword's row in the table
                $('#wr_' + index + '_' + $('#tabWR_' + index).find('.trWR').length).click(function(e) {
                    $(this).parent('td').parent('tr').remove();
                    addClassVAlign();
                    verticalAlignCenter();
                    e.preventDefault();
                    return false;
                });
            }

        });
        //***************************

        if (nbResponses == 0) {
            $('#newTable').find('.trHole:last')
                .append('<td class="classic"><a id="hole_' + index + '" href="#" class="btn btn-danger">' + langDel + '</a></td>'
            );

            $('#hole_' + index).click(function (e) {
                var ind = $(this).attr('id');
                ind = ind.replace('hole_', '');

                nodeblank = tinyMCE.get('ujm_exobundle_interactionholetype_html').selection
                    .select(tinyMCE.get('ujm_exobundle_interactionholetype_html').dom.select('#' + ind)[0]);

                if(nodeblank) {
                    tinyMCE.get('ujm_exobundle_interactionholetype_html').selection.setContent(nodeblank.value);
                }

                $(this).parent('td').parent('tr').remove();
                e.preventDefault();
                return false;
            });
        }

        $('#ujm_exobundle_interactionholetype_holes_' + nbHole + '_selector').change(function (e) {
            var ind = $(this).parent('td').parent('tr').find('td:first').find('input:first').val();

            var node = tinyMCE.get('ujm_exobundle_interactionholetype_html').selection
                    .select(tinyMCE.get('ujm_exobundle_interactionholetype_html').dom.select('#' + ind)[0]);

            if ($(this).is(':checked')) {

                if (node.tagName == 'INPUT') {
                    nodeselect = '<select class="blank" id="' + ind + '" name="blank_' + ind + '"><option>' + node.value + '</option></select>';
                    tinyMCE.get('ujm_exobundle_interactionholetype_html').selection.setContent(nodeselect);
                }

            } else {
                if (node.tagName == 'SELECT') {
                    idSize = ind - 1;
                    size = $('#ujm_exobundle_interactionholetype_holes_' + idSize + '_size').val();
                    nodeBlank = '<input type="text" value="' + node.value + '" size="' + size + '" class="blank" id="' + ind + '" name="blank_' + ind + '" autocomplete="off">';
                    tinyMCE.get('ujm_exobundle_interactionholetype_html').selection.setContent(nodeBlank);
                }
            }
            e.preventDefault(); // prevent add # in the url
            return false;
        });
        changeSize($('#newTable').find('.trHole:last').index(), index);
    });

    container.remove();
    $('#prototypes').remove();

    addClassVAlign();
    verticalAlignCenter();
}

function createHole() {
    var blank = $.trim(tinyMCE.get('ujm_exobundle_interactionholetype_html').selection.getContent({format : 'text'}));
    blank = blank.replace(/\s{2,}/g, ' ');

    if (blank != '') {

        var nbHole = tinyMCE.activeEditor.dom.select('.blank').length;
        var indexBlank = 1;

        if (nbHole > 0) {
            tinymce.each(tinyMCE.activeEditor.dom.select('.blank'), function (n) {
                if (indexBlank <= n.id) {
                    indexBlank = parseInt(n.id) + 1;
                }
            });
        }

        var el = tinyMCE.activeEditor.dom
            .create('input', {'id' : indexBlank, 'name' : 'blank_'+indexBlank, 'type' : 'text', 'size' : '15', 'value' : blank, 'class' : 'blank', 'autocomplete' : 'off'});

        tinyMCE.activeEditor.selection.setNode(el);

        addHole(indexBlank, blank);
    } else {
        DisplayInstruction();
    }
}

function addHole(indexBlank, valHole) {
    var uniqChoiceID = false;

    //var index = $('#newTable').find('.trHole').length;
    var index = indexBlank;

    $('#newTable').find('tbody').append('<tr class="trHole"></tr>');

    /*while (uniqChoiceID == false) {
        if ($('#ujm_exobundle_interactionholetype_holes_' + index + '_label').length) {
            index++;
        } else {
            uniqChoiceID = true;
        }
    }*/

    container.append(
        $(container.attr('data-prototype').replace(/__name__/g, index))
    );

    container.find('.row').each(function () {
        if ($(this).find('input').length) {
            //not yet implemented so don't create
             if ($(this).find('input').attr("id").indexOf('orthography') == -1) {
                $('#newTable').find('tr:last').append('<td class="classic"></td>');
                $('#newTable').find('td:last').append($(this).find('input'));
            } else {
                $('#newTable').find('tr:last').append('<td class="classic" style="display:none;"></td>');
                $('#newTable').find('td:last').append($(this).find('input'));
            }
        }
    });

    $('#newTable').find('tr:last').find('td:first').css('display', 'none');
    $('#newTable').find('tr:last').find('td:first').find('input:first').val(indexBlank);

    $('#ujm_exobundle_interactionholetype_holes_' + index + '_size').val('15');

    // Remove the useless fields form
    container.remove();

    var addwr = '<a href="#" id="add_keyword_' + index + '" class="btn btn-primary"><i class="fa fa-plus"></i>&nbsp;' + langKeyWord + '</a>';
    $('#newTable').find('tr:last').append('<td class="classic"><table id="tabWR_' + index + '"><tbody></tbody></table>' + addwr + '</td>');

    addWR(index, index);

    $('#ujm_exobundle_interactionholetype_holes_' + index + '_wordResponses_0_response').val(valHole);
    //$('#ujm_exobundle_interactionholetype_holes_'+index+'_wordResponses_0_response').attr("readonly", true);
    //$('#ujm_exobundle_interactionholetype_holes_' + index + '_wordResponses_0_score').attr("placeholder", langPoint);

    // Remove the useless fileds form
    containerWR.remove();

    // Add delete button for hole
    $('#newTable').find('.trHole:last')
        .append('<td class="classic"><a id="hole_' + indexBlank + '" href="#" class="btn btn-danger">' + langDel + '</a></td>'
    );

    // When click, delete the matching hole's row in the table
    $('#hole_' + indexBlank).click(function (e) {
        nodeblank = tinyMCE.get('ujm_exobundle_interactionholetype_html').selection
                .select(tinyMCE.get('ujm_exobundle_interactionholetype_html').dom.select('#' + indexBlank)[0]);

        if(nodeblank) {
            tinyMCE.get('ujm_exobundle_interactionholetype_html').selection.setContent(nodeblank.value);
        }

        $(this).parent('td').parent('tr').remove();
        e.preventDefault();

        return false;
    });

    $('#add_keyword_' + index).click(function (e) {
        addWR(index, index);
        e.preventDefault(); // prevent add # in the url
        return false;
    });

    $('#ujm_exobundle_interactionholetype_holes_' + index + '_selector').change(function (e) {
        var node = tinyMCE.get('ujm_exobundle_interactionholetype_html').selection
                .select(tinyMCE.get('ujm_exobundle_interactionholetype_html').dom.select('#' + indexBlank)[0]);

        if ($(this).is(':checked')) {

            if (node.tagName == 'INPUT') {
                nodeselect = '<select class="blank" id="' + indexBlank + '" name="blank_' + indexBlank + '"><option>' + node.value + '</option></select>';
                tinyMCE.get('ujm_exobundle_interactionholetype_html').selection.setContent(nodeselect);
            }

        } else {
            if (node.tagName == 'SELECT') {
                size = $('#ujm_exobundle_interactionholetype_holes_' + index + '_size').val();
                nodeBlank = '<input type="text" value="' + node.value + '" size="' + size + '" class="blank" id="' + index + '" name="blank_' + indexBlank + '" autocomplete="off">';
                tinyMCE.get('ujm_exobundle_interactionholetype_html').selection.setContent(nodeBlank);
            }
        }
        e.preventDefault(); // prevent add # in the url
        return false;
    });

    changeSize(index, index);
    disableNotYetReady();

    if (($('#newTable').find('td').length) > 1) {
        $('#newTable').css({"display" : "block"});
    } else {
        $('#newTable').css({"display" : "none"});
    }
}

function addWR(indexHole, idTabWR) {
    addClassVAlign();

    //wordResponse
    uniqChoiceID = false;
    indexWR = $('#tabWR_' + idTabWR).find('.trWR').length;

    while (uniqChoiceID == false) {
        if ($('#ujm_exobundle_interactionholetype_holes_' + indexHole + '_wordResponses_' + indexWR + '_response').length) {
            indexWR++;
        } else {
            uniqChoiceID = true;
        }
    }

    $('#tabWR_'+idTabWR).find('tbody').append('<tr class="trWR"></tr>');

    //alert(containerWR.attr('data-prototype').valueOf());
    containerWR.append(
        $(containerWR.attr('data-prototype')
        .replace(/holes___name__/g, 'holes_' + indexHole)
        .replace(/wordResponses___name__/g, 'wordResponses_' + indexWR)
        .replace(/\[holes\]\[__name__\]/g, '[holes][' + indexHole + ']')
        .replace(/\[wordResponses\]\[__name__\]/g, '[wordResponses][' + indexWR + ']'))
   );

    containerWR.find('.row').each(function () {
        if ($(this).find('input').length) {
            $('#tabWR_'+idTabWR).find('tr:last').append('<td class="classic"></td>');
            $('#tabWR_'+idTabWR).find('td:last').append($(this).find('input'));
        }     
        //Add the field of type textarea feedback
//        addFeedback($(this),indexHole,idTabWR);   
    });

    if (indexWR > 0) {
        $('#tabWR_' + idTabWR).find('tr:last').append('<td class="classic"></td>');
        $('#tabWR_' + idTabWR).find('td:last').append(
            '<a id="wr_' + indexHole + '_' + indexWR + '" href="#" class="btn btn-default"><i style="color : red" class="fa fa-trash-o"></i></a>'
        );

        // When click, delete the matching keyword's row in the table
        $('#wr_' + indexHole + '_' + indexWR).click(function(e) {
            $(this).parent('td').parent('tr').remove();
            addClassVAlign();
            verticalAlignCenter();
            e.preventDefault();
            return false;
        });
    }
    
    $('#ujm_exobundle_interactionholetype_holes_' + indexHole + '_wordResponses_' + indexWR + '_response').attr("placeholder", langKeyWord);
    //$('#ujm_exobundle_interactionholetype_holes_' + indexHole + '_wordResponses_' + indexWR + '_score').attr("placeholder", langPoint);

    $('#ujm_exobundle_interactionholetype_holes_' + indexHole + '_wordResponses_' + indexWR + '_response').focusout(function() {
        $(this).val($.trim($(this).val()));
        $(this).val($(this).val().replace(/\s{2,}/g, ' '));
    });

    verticalAlignCenter();
}

function addClassVAlign() {
    $('#newTable').find('td').each(function () {
        $(this).children('input').addClass('vertical-align-center');
        //$(this).children('a').addClass('vertical-align-center');
    });
}

function verticalAlignCenter() {
    $(".vertical-align-center").each(function () {
        var $elem = $(this);
        var elemHeight = $elem.height();
        if (elemHeight == 0)	// perhap's an element is no loaded
                return;
        var $container = $elem.parent();
        var marginTop = Math.floor(($container.height() - elemHeight) / 2);
        if (marginTop > 0)
            $elem.css("margin-top", marginTop);
        $elem.removeClass("vertical-align-center");
    });
}

// Check if form is valid
function check_form(nbHole) {
    if (($('#newTable').find('tr:not(:first)').length) < 1) {
        alert(nbHole);
        return false;
    }
}

function changeSize (idSize, indexBlank) {
    $('#ujm_exobundle_interactionholetype_holes_' + idSize + '_size').change(function (e) {
        var blank = tinyMCE.get('ujm_exobundle_interactionholetype_html').selection
                .select(tinyMCE.get('ujm_exobundle_interactionholetype_html').dom.select('#' + indexBlank)[0]);

        if (blank.tagName == 'INPUT') {
            var newBlank = '<input type="text" value="' + blank.value + '" size="' + $(this).val() + '" class="blank" id="' + indexBlank + '" name="blank_' + indexBlank + '">';
            tinyMCE.get('ujm_exobundle_interactionholetype_html').selection.setContent(newBlank);
        }

        e.preventDefault(); // prevent add # in the url
        return false;
    });
}

$(document).ready(function() {
    $('#holeEditor').bind("DOMSubtreeModified",function(e) {

        if ( (tinyMCE.activeEditor.selection) && (tinyMCE.activeEditor.selection.getContent() == '') ) {
            $('#newTable').find(('.trHole')).each(function () {
                index = $(this).find('td:first').find('input:first').val();
                if ( (index) && (!tinyMCE.get('ujm_exobundle_interactionholetype_html').dom.select('#' + index)[0]) ) {
                    $(this).remove();
                }
            });
        }
    });
});

//not yet implemented
function disableNotYetReady() {
    $('#newTable').find(('.trHole')).each(function () {
        //$(this).find('input').eq(1).attr("readonly", true);
//        $(this).find('input').eq(2).attr("disabled", true);
        $(this).find('input').eq(2).attr("style", "display:none;");
    });
}
//*******************

/**
 * Add the field of type textarea feedback
 * @param {type} row
 * @param {type} indexHole
 * @param {type} idTabWR
 */
function addFeedback(row,indexHole,idTabWR)
{
     //Add the field of type textarea feedback
        if (row.find('*[id$="_feedback"]').length) {
       
            var idFeedbackVal = row.find('textarea').attr("id");
            //Adds a cell array with a comment button
            $('#tabWR_' + idTabWR).find('tr:last').append('<td class="classic"><a class="btn btn-default" title="'+langComment+'" id="btn_' + idFeedbackVal + '" onClick="addTextareaFeedback(\'span_' + idFeedbackVal + '\',\'btn_' + idFeedbackVal + '\')" ><i class="fa fa-comments-o"></i></a><span id="span_' + idFeedbackVal + '" class="input-group" style="display:none;"></span></td>');
            //Adds the textarea and its advanced edition button (hidden by default)
            $('#span_' + idFeedbackVal).append(row.find('*[id$="_feedback"]'));
            $('#span_' + idFeedbackVal).append('<span class="input-group-btn"><a class="btn btn-default" id="btnEdition_' + idFeedbackVal + '" onClick="advancedEdition(\'ujm_exobundle_interactionholetype_holes_' + indexHole + '_wordResponses_' + indexWR + '_feedback\',\'btnEdition_' + idFeedbackVal + '\',event);" title="' + langEdition + '"><i class="fa fa-font"></i></a></span>');
        }   
}