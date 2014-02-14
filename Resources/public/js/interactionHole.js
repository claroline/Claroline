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

function addFormHole(add, response, point, size, orthography, del, selector, source_image_add, wlangKeyWord, wlangPoint) {
    langKeyWord = wlangKeyWord;
    langPoint   = wlangPoint;
    langDel     = del;

    tableHoles.append('<table id="newTable" class="table table-striped table-bordered table-condensed"><thead><tr style="background-color: lightsteelblue;"><th class="classic">' + size + '</th><th class="classic">' + orthography + '</th><th class="classic">' + selector + '</th><th class="classic">' + response + '</th><th class="classic">' + del + '</th></tr></thead><tbody></tbody></table>');
    $('tbody').sortable();
}

function addFormHoleEdit(add, response, point, size, orthography, del, selector, source_image_add, wlangKeyWord, wlangPoint) {
    langKeyWord = wlangKeyWord;
    langPoint   = wlangPoint;
    langDel     = del;
    var index;
    var i = 0;

    tableHoles.append('<table id="newTable" class="table table-striped table-bordered table-condensed"><thead><tr style="background-color: lightsteelblue;"><th class="classic">' + size + '</th><th class="classic">' + orthography + '</th><th class="classic">' + selector + '</th><th class="classic">' + response + '</th><th class="classic">' + del + '</th></tr></thead><tbody class="bodyHole"></tbody></table>');
    $('tbody').sortable();

    container.children().first().children('div').each(function () {

        var nbHole = $('#newTable').find('.trHole').length;

        // Add a row to the table
        $('#newTable').find('.bodyHole').append('<tr class="trHole"></tr>');

         $(this).find('.row').each(function () {

            if ($(this).find('input').length) {
                $('#newTable').find('tr:last').append('<td class="classic"></td>');
                $('#newTable').find('td:last').append($(this).find('input'));
            }

        });

        $('#newTable').find('tr:last').find('td:first').css('display', 'none');
        index = $('#newTable').find('tr:last').find('td:first').find('input:first').val();

        /*if (nbResponses == 0) {
            // Add the delete button
            $('#newTable').find('tr:last').append('<td class="classic"></td>');
            addDelete($('#newTable').find('td:last'), deleteChoice);
        }*/
        
        //******For Key words********
        var addwr = '<a href="#" id="add_keyword_' + index + '" class="btn btn-primary"><i class="icon-plus"></i>&nbsp;' + langKeyWord + '</a>';
        
        $('#newTable').find('.trHole:last').find('td:last')
            .append('<table id="tabWR_' + index + '"><tbody></tbody></table>' + addwr);
    
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
            if (i == 0) {
                $('#tabWR_'+index).find('tbody').append('<tr class="trWR"></tr>');
            } else if (i>1) {
                i = 0;
                $('#tabWR_'+index).find('tbody').append('<tr class="trWR"></tr>');
            }
             
            $('#tabWR_' + index).find('tr:last').append('<td class="classic"></td>');
            $(this).appendTo($('#tabWR_' + index).find('tr:last').find('td:last'));
            i++;
            
            //add buton delete for a key word
            if ( (i>1) && ($('#tabWR_' + index).find('.trWR').length > 1)) {
                $('#tabWR_' + index).find('tr:last').append('<td class="classic"></td>');
                $('#tabWR_' + index).find('td:last').append(
                    '<a id="wr_' + index + '_' + $('#tabWR_' + index).find('.trWR').length + '" href="#" class="btn btn-danger">' + langDel + '</a>'
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

        $('#ujm_exobundle_interactionholetype_holes_' + nbHole + '_selector').change(function (e) {
            var ind = $(this).parent('td').parent('tr').find('td:first').find('input:first').val();

            var node = tinyMCE.get('ujm_exobundle_interactionholetype_html').selection
                    .select(tinyMCE.get('ujm_exobundle_interactionholetype_html').dom.select('#' + ind)[0]);

            if ($(this).attr('checked')) {

                if (node.tagName == 'INPUT') {
                    nodeselect = '<select id="' + ind + '"><option>' + node.value + '</option></select>';
                    tinyMCE.get('ujm_exobundle_interactionholetype_html').selection.setContent(nodeselect);
                } 

            } else {
                if (node.tagName == 'SELECT') {
                    size = $('#ujm_exobundle_interactionholetype_holes_' + ind + '_size').valueOf();
                    nodeBlank = '<input type="text" value="' + node.value + '" size="' + size + '" class="blank" id="' + ind + '">';
                    tinyMCE.get('ujm_exobundle_interactionholetype_html').selection.setContent(nodeBlank);
                }
            }
            e.preventDefault(); // prevent add # in the url
            return false;
        });
        
    });
    
    container.remove();
    $('#prototypes').remove();
    
    addClassVAlign();
    verticalAlignCenter();
}

function createHole() {
    var blank = tinyMCE.activeEditor.selection.getContent({format : 'text'});
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
        .create('input', {'id' : indexBlank, 'type' : 'text', 'size' : '15', 'value' : blank, 'class' : 'blank'});

    tinyMCE.activeEditor.selection.setNode(el);

    addHole(indexBlank, blank);
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
            $('#newTable').find('tr:last').append('<td class="classic"></td>');
            $('#newTable').find('td:last').append($(this).find('input'));
        }
    });

    $('#newTable').find('tr:last').find('td:first').css('display', 'none');
    $('#newTable').find('tr:last').find('td:first').find('input:first').val(indexBlank);

    $('#ujm_exobundle_interactionholetype_holes_' + index + '_size').val('15');

    // Remove the useless fields form
    container.remove();

    var addwr = '<a href="#" id="add_keyword_' + index + '" class="btn btn-primary"><i class="icon-plus"></i>&nbsp;' + langKeyWord + '</a>';
    $('#newTable').find('tr:last').append('<td class="classic"><table id="tabWR_' + index + '"><tbody></tbody></table>' + addwr + '</td>');

    addWR(index, index);

    $('#ujm_exobundle_interactionholetype_holes_' + index + '_wordResponses_0_response').val(valHole);
    //$('#ujm_exobundle_interactionholetype_holes_'+index+'_wordResponses_0_response').attr("readonly", true);
    $('#ujm_exobundle_interactionholetype_holes_' + index + '_wordResponses_0_score').attr("placeholder", langPoint);

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
        
        if ($(this).attr('checked')) {
        
            if (node.tagName == 'INPUT') {
                nodeselect = '<select id="' + indexBlank + '"><option>' + node.value + '</option></select>';
                tinyMCE.get('ujm_exobundle_interactionholetype_html').selection.setContent(nodeselect);
            } 
            
        } else {
            if (node.tagName == 'SELECT') {
                size = $('#ujm_exobundle_interactionholetype_holes_' + index + '_size').valueOf();
                nodeBlank = '<input type="text" value="' + node.value + '" size="' + size + '" class="blank" id="' + index + '">';
                tinyMCE.get('ujm_exobundle_interactionholetype_html').selection.setContent(nodeBlank);
            }
        }
        e.preventDefault(); // prevent add # in the url
        return false;
    });
    
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
    /*containerWR.append(
        $(containerWR.attr('data-prototype').replace(/__name__/, 8))
    );*/

    containerWR.find('.row').each(function () {
        if ($(this).find('input').length) {
            $('#tabWR_'+idTabWR).find('tr:last').append('<td class="classic"></td>');
            $('#tabWR_'+idTabWR).find('td:last').append($(this).find('input'));
        }
    });

    if (indexWR > 0) {
        $('#tabWR_' + idTabWR).find('tr:last').append('<td class="classic"></td>');
        $('#tabWR_' + idTabWR).find('td:last').append(
            '<a id="wr_' + indexHole + '_' + indexWR + '" href="#" class="btn btn-danger">' + langDel + '</a>'
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
    $('#ujm_exobundle_interactionholetype_holes_' + indexHole + '_wordResponses_' + indexWR + '_score').attr("placeholder", langPoint);

    verticalAlignCenter();
}

function addClassVAlign() {
    $('#newTable').find('td').each(function () {
        $(this).children('input').addClass('vertical-align-center');
    });
    $('#hole_' + index).addClass('vertical-align-center');
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

// Set the hole order
/*function setOrder() {

    var order = 1;

    $('#newTable').find('.trhole').each(function () {
        $(this).find('input:first').val(order);
        order++;
    });
}*/

// Check if form is valid
function check_form(nbHole) {
    if (($('#newTable').find('tr:not(:first)').length) < 1) {
        alert(nbHole);
        return false;
    }
}