function insert_style(){

    $('#ujm_exobundle_interactionholetype_interaction').find('div').first().find('label').first().remove();
    
}

//css hole
function hole_css(indHole)
{
    var $orderNewHole;
    var $tabInput = $('#ujm_exobundle_interactionholetype_html').val().split('<input');
    for (var i = 1; i<=$tabInput.length - 1; i++) {
        if ($tabInput.length >1){
            if($tabInput[i].split('"')[1] == indHole)
            {
                $orderNewHole = i;
            }
        }
    }
        
    var $pos = $orderNewHole - 1;
    var $nbr = 1;
    var $indRow;
    $('#ujm_exobundle_interactionholetype_holes').children('div').each(function(index) {
            if($pos > 0 )
            {
                $('#newTable .ligne_hole').each(function(index) {
                    if($pos == $nbr)
                    {
                        $indRow = $(this).attr('id').substring(3);
                        //exit the loop
                        return false;
                    }
                    $nbr++;
                });
                $('#ntr'+$indRow).after('<tr class="ligne_hole" id="ntr'+ indHole +'">  </tr>');
            }
            else
            {
                $('#newTable').append('<tr class="ligne_hole" id="ntr'+ indHole +'">  </tr>');
            }
            $('#ntr'+indHole).append($(this));
    });

    $('#ntr'+indHole).append('<td class="colonne_hole_'+indHole+'" >  </td>');
    $('#newTable .colonne_hole_'+indHole+':last').append($('#ntr'+indHole).children('div').children('label').first());
    $('#ntr'+indHole).children('div').children('div').children('div').each(function(index) {
            $('#ntr'+indHole).append('<td class="colonne_hole_'+indHole+'" >  </td>');
            $('#newTable .colonne_hole_'+indHole+':last').append($(this));
    });

    $('#ntr'+indHole).find('div').first().remove()

    // css th    
    $('#newTable th').css({
            "background-color": "#eee"           
    });

    //ajout de la derniere colonne pr l'ajout et la supression 
    $('#ntr'+indHole).contents('td:last').after('<td style="display: none;"><a href="#" id="delete_hole_'+indHole+'">supprimer</a> </td> '); 

    // clique boutons supprimer lignes du tableau
    $('#delete_hole_'+indHole).live('click', function () {
        $(this).parents('tr.ligne_hole:first').remove();
        var i = 0;
        $('#newTable .ligne_hole').each(function(index) {
            $(this).find('label:first').text(i);
            i = i + 1;
        });
        if ($(this).attr("href") == "#") {
            return false;
        }
    });


    //css td
    $('#newTable tr').contents('td').css({'border': '1px solid #aaaaaa'});

    $("td:hidden").find('input').attr('value','1');

}

function hole_css_edit(add, source_image_add, nbResponses)
{   
    indexHole = 0;
    var $row;
    var $idHole = 1;
    $('#ujm_exobundle_interactionholetype_holes').children('div').each(function(index) {
            //récupération des id des élément input pour les blancs
            $tabInput = $('#ujm_exobundle_interactionholetype_html').val().split('<input');
            $input = $tabInput[$idHole].split('"');
            
            $('#newTable').append('<tr class="ligne_hole" id="ntr'+ $input[1] +'">  </tr>');
            $('#newTable .ligne_hole:last').append($(this)); 
            $idHole++;
    });

    $('#newTable .ligne_hole').each(function(index) {
            $(this).append('<td class="colonne_hole_'+$(this).attr('id').substring(3)+'" >  </td>');
            $(this).children('td').first().append($(this).children('div').children('label').first());
            
            $row = $(this);
            $row.children('div').children('div').children('div').each(function(index) {
                $row.append('<td class="colonne_hole_'+$row.attr('id').substring(3)+'" >  </td>');
                $row.children('td').last().append($(this));
            });
    });

    /*$('#newTable .ligne_hole').each(function(index) {
        var row = $(this);
        row.children('div').children('div').children('div').each(function(index) {
                row.append('<td class="colonne_hole" >  </td>');
                row.children('td').last().append($(this));
        });
    });*/

    
    $('#newTable .ligne_hole').each(function(index) {
        $(this).find('div').first().remove();   
    });


    // css th    
    $('#newTable th').css({
            "background-color": "#eee"           
    });

    //ajout de la derniere colonne pr l'ajout et la supression
    $idHole = 1;
    $('#newTable .ligne_hole').each(function(index) {
        //récupération des id des élément input pour les blancs
        $tabInput = $('#ujm_exobundle_interactionholetype_html').val().split('<input');
        $input = $tabInput[$idHole].split('"');

        $(this).contents('td:last').after('<td style="display: none;"><a href="#" id="delete_hole_'+$input[1]+'">supprimer</a> </td> ');
        $idHole++;
            
        // clique boutons supprimer lignes du tableau
        $('#delete_hole_'+$input[1]).live('click', function () {
            $(this).parents('tr.ligne_hole:first').remove();
            //changer lindex de la ligne
            var i = 0;
            $('#newTable .ligne_hole').each(function(index) {
                $(this).find('label:first').text(i);
                i = i + 1;
            });
            if ($(this).attr("href") == "#") {
                return false;
            }
        });
        
        //Mise en forme des formulaires wordResponse
        add_form_word_edit(add, source_image_add, indexHole, nbResponses);
        word_css_edit(nbResponses, indexHole);
        indexHole++;
    });

    //css td
    $('#newTable tr').contents('td').css({'border': '1px solid #aaaaaa'});

}

//add holes
function add_form_hole(add, Response, point, size, orthography, selector, source_image_add) 
{

//$('#ujm_exobundle_interactionholetype_holes').before('<a href="#" id="add_hole"><img src="' + source_image_add + '">'+add+'</a>');
     
$('#ujm_exobundle_interactionholetype_holes').after('<table style="border: 1px solid black;" id="newTable"><tr> <th>ID</th> <th>'+size+'</th> <th>'+orthography+'</th> <th>'+selector+'</th> <th>'+Response+'</th><th style="display: none;">------</th> </tr></table>');
$('#add_hole').css({
        "display":"block",
        "color": "green",
        "float":"right"
        });

/*if($('#newTable').children('.ligne_hole').length == 0) {
    add_hole(add, source_image_add);     
}*/ 

hole_css(0);

/*$('#add_hole').click(function() {
    add_hole(add, source_image_add);
    if ($(this).attr("href") == "#") {
        return false;
    }
});*/

}
		
function add_form_hole_edit(add, Response, point, size, orthography, selector, source_image_add, nbResponses) 
{
    
    /*if (nbResponses == 0){
        $('#ujm_exobundle_interactionholetype_holes').before('<a href="#" id="add_hole"><img src="' + source_image_add + '">'+add+'</a>');
    }*/


    $('#ujm_exobundle_interactionholetype_holes').after('<table style="border: 1px solid black;" id="newTable"><tr> <th>ID</th> <th>'+size+'</th> <th>'+orthography+'</th> <th>'+selector+'</th> <th>'+Response+'</th><th style="display: none;">------</th> </tr></table>');
    $('#add_hole').css({
            "display":"block",
            "color": "green",
            "float":"right"
            });

    $('#add_hole').click(function() {
        add_hole(add, source_image_add);
    if ($(this).attr("href") == "#") {
        return false;
    }
    });

}

function add_hole(add, source_image_add, indHole, valHole) {
    var $container = $('#ujm_exobundle_interactionholetype_holes');
    index = $('#newTable .ligne_hole').length;

    $container.append(
        $($container.attr('data-prototype').replace(/__name__/g, index))
    );
    
    $('#ujm_exobundle_interactionholetype_holes_'+index+'_size').val('15');
    
    add_form_word(add, source_image_add, index);
    $('#ujm_exobundle_interactionholetype_holes_'+index+'_wordResponses_0_response').val(valHole);
    hole_css(indHole);
}

//add wordResponses
function add_form_word(add, source_image_add, indexHole) 
{

$('#ujm_exobundle_interactionholetype_holes_'+indexHole+'_wordResponses').before('<a href="#" id="add_wordresponse'+indexHole+'"><img src="' + source_image_add + '">'+add+'</a>');
     
$('#ujm_exobundle_interactionholetype_holes_'+indexHole+'_wordResponses').after('<table style="border: 1px solid black;" id="newTableWord'+indexHole+'"></table>');
$('#add_wordresponse'+indexHole).css({
        "display":"block",
        "color": "green",
        "float":"right"
        });

if($('#newTableWord'+indexHole).children('.ligne_wordresponse').length == 0) {
    add_wordresponse(indexHole);     
} 

$('#add_wordresponse'+indexHole).click(function() {
    add_wordresponse(indexHole);
    if ($(this).attr("href") == "#") {
        return false;
    }
});

}

function add_form_word_edit(add, source_image_add, indexHole, nbResponses) 
{

    if (nbResponses == 0){
        $('#ujm_exobundle_interactionholetype_holes_'+indexHole+'_wordResponses').before('<a href="#" id="add_wordresponse'+indexHole+'"><img src="' + source_image_add + '">'+add+'</a>');
    }


    $('#ujm_exobundle_interactionholetype_holes_'+indexHole+'_wordResponses').after('<table style="border: 1px solid black;" id="newTableWord'+indexHole+'"></table>');
    $('#add_wordresponse'+indexHole).css({
            "display":"block",
            "color": "green",
            "float":"right"
            });

    $('#add_wordresponse'+indexHole).click(function() {
        add_wordresponse_edit(indexHole);
        if ($(this).attr("href") == "#") {
            return false;
        }
    });

}

function add_wordresponse(indexHole) {
    var $container = $('#ujm_exobundle_interactionholetype_holes_'+indexHole+'_wordResponses');
    var prototype = $container.attr('data-prototype');
    
    indexWord = $('#newTableWord'+indexHole+' .ligne_wordresponse').length;

    //le prototype de wordresponse est encapsulé dans celui de hole l'expression régulière appelée dans add_hole a donc modifié le prototype de wordreponse
    var re = new RegExp("wordResponses_"+indexHole,"g");
    var re2 = new RegExp("\\[wordResponses\\]\\["+indexHole+"\\]","g");
    
    var newForm = prototype.replace(re, 'wordResponses_'+indexWord);
    newForm = newForm.replace(re2, '[wordResponses]['+indexWord+']');
    
    $container.append(newForm);
        
    word_css(indexHole);
}

function add_wordresponse_edit(indexHole) {
    var $container = $('#ujm_exobundle_interactionholetype_holes_'+indexHole+'_wordResponses');
    var prototype = $container.attr('data-prototype');
    
    indexWord = $('#newTableWord'+indexHole+' .ligne_wordresponse').length;

    var newForm = prototype.replace(/__name__/g, indexWord);

    $container.append(newForm);
        
    word_css(indexHole);
}

//css word
function word_css(indexHole)
{   
    $('#ujm_exobundle_interactionholetype_holes_'+indexHole+'_wordResponses').children('div').each(function() {
            $('#newTableWord'+indexHole).append('<tr class="ligne_wordresponse" >  </tr>');
            $('#newTableWord'+indexHole+' .ligne_wordresponse:last').append($(this));       
    });

    $('#newTableWord'+indexHole+' .ligne_wordresponse:last').append('<td class="colonne_wordresponse" >  </td>');
    $('#newTableWord'+indexHole+' .colonne_wordresponse:last').append($('#newTableWord'+indexHole+'.ligne_wordresponse:last').children('div').children('label').first());
    $('#newTableWord'+indexHole+' .ligne_wordresponse:last').children('div').children('div').children('div').each(function() {
            $('#newTableWord'+indexHole+' .ligne_wordresponse:last').append('<td class="colonne_hole" >  </td>');
            $('#newTableWord'+indexHole+' .colonne_hole:last').append($(this));
    });

    $('#newTableWord'+indexHole+' .ligne_wordresponse:last').find('div').first().remove()

    // css th    
    $('#newTableWord'+indexHole+' th').css({
            "background-color": "#eee"           
    });

    //ajout de la derniere colonne pr l'ajout et la supression 
    $('#newTableWord'+indexHole+' .ligne_wordresponse:last').contents('td:last').after('<td><a href="#" id="delete_word'+indexHole+'">supprimer</a> </td> '); 

    // clique boutons supprimer lignes du tableau
    $('#delete_word'+indexHole).live('click', function () {
        $(this).parents('tr.ligne_wordresponse:first').remove();
        var i = 0;
        $('#newTable .ligne_wordresponse').each(function() {
            $(this).find('label:first').text(i);
            i = i + 1;
        });
        if ($(this).attr("href") == "#") {
            return false;
        }
    });


    //css td
    $('#newTableWord'+indexHole+' tr').contents('td').css({'border': '1px solid #aaaaaa'});

    $("td:hidden").find('input').attr('value','1');

}

function word_css_edit(nbResponses, indexHole)
{
    
    $('#ujm_exobundle_interactionholetype_holes_'+indexHole+'_wordResponses').children('div').each(function(index) {
            $('#newTableWord'+indexHole).append('<tr class="ligne_wordresponse" >  </tr>');
            $('#newTableWord'+indexHole+' .ligne_wordresponse:last').append($(this));   
    });

    $('#newTableWord'+indexHole+' .ligne_wordresponse').each(function(index) {
            $(this).append('<td class="colonne_wordresponse" >  </td>');
            $(this).children('td').first().append($(this).children('div').children('label').first());
    });

    $('#newTableWord'+indexHole+' .ligne_wordresponse').each(function(index) {
        var row = $(this);
        row.children('div').children('div').children('div').each(function(index) {
                row.append('<td class="colonne_wordresponse" >  </td>');
                row.children('td').last().append($(this));
        });
    });

    
    $('#newTableWord'+indexHole+' .ligne_wordresponse').each(function(index) {
        $(this).find('div').first().remove();   
    });


    // css th    
    $('#newTableWord'+indexHole+' th').css({
            "background-color": "#eee"           
    });

    var numWord = 0;
    //ajout de la derniere colonne pr l'ajout et la supression
    $('#newTableWord'+indexHole+' .ligne_wordresponse').each(function(index) {
        if(nbResponses == 0)
        {
            $(this).contents('td:last').after('<td class="remove_word"><a href="#" id="delete_word'+indexHole+'">supprimer</a> </td> ');
        }
        else
        {
            $(this).contents('td:last').after('<td>supprimer</td> ');
        }  
        numWord++;
    });

    // clique boutons supprimer lignes du tableau
    $('#delete_word'+indexHole).live('click', function () {
        $(this).parents('tr.ligne_wordresponse:first').remove();
        //changer lindex de la ligne
        var i = 0;
        $('#newTableWord'+indexHole+' .ligne_wordresponse').each(function(index) {
            $(this).find('label:first').text(i);
            i = i + 1;
        });
        if ($(this).attr("href") == "#") {
            return false;
        }
    });


    //css td
    $('#newTableWord'+indexHole+' tr').contents('td').css({'border': '1px solid #aaaaaa'});

}