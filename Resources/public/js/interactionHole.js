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

function addFormHole(add, Response, point, size, orthography, del, selector, source_image_add, wlangKeyWord, wlangPoint)
{
    langKeyWord = wlangKeyWord;
    langPoint   = wlangPoint;
    langDel     = del;
    
    tableHoles.append('<table id="newTable" class="table table-striped table-bordered table-condensed"><thead><tr style="background-color: lightsteelblue;"><th class="classic">'+size+'</th><th class="classic">'+orthography+'</th><th class="classic">'+selector+'</th><th class="classic">'+Response+'</th><th class="classic">'+del+'</th></tr></thead><tbody></tbody></table>');
    $('tbody').sortable();
}

function createHole()
{
    var blank = tinyMCE.activeEditor.selection.getContent({format : 'text'});
        
    var nbHole = tinyMCE.activeEditor.dom.select('.blank').length;
    var indexHole = 1;

    if(nbHole > 0) {
        tinymce.each(tinyMCE.activeEditor.dom.select('.blank'), function(n) {
            if(indexHole <= n.id) {
                indexHole = parseInt(n.id)+1;
            }
        });
    }
    
    var el = tinyMCE.activeEditor.dom
            .create('input', {'id' : indexHole, 'type' : 'text', 'size' : '15', 'value' : blank, 'class' : 'blank'});
    
    tinyMCE.activeEditor.selection.setNode(el);
    
    addHole(indexHole - 1, blank);
}

function addHole(indHole, valHole)
{
    var uniqChoiceID = false;

    var index = $('#newTable').find('.trHole').length;
    var indexWR;
    
    $('#newTable').find('tbody').append('<tr class="trHole"></tr>');

    while (uniqChoiceID == false) {
        if ($('#ujm_exobundle_interactionholetype_holes_' + index + '_label').length) {
            index++;
        } else {
            uniqChoiceID = true;
        }
    }

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
    
    $('#ujm_exobundle_interactionholetype_holes_'+index+'_size').val('15');
    
    // Remove the useless fileds form
    container.remove();
    
    var addwr = '<a href="#" id="add_keyword_'+index+'" class="btn btn-primary"><i class="icon-plus"></i>&nbsp;'+langKeyWord+'</a>';
    $('#newTable').find('tr:last').append('<td class="classic"><table id="tabWR_'+index+'"><tbody></tbody></table>'+addwr+'</td>');
    
    addWR(index);
    
    $('#ujm_exobundle_interactionholetype_holes_'+index+'_wordResponses_0_response').val(valHole);
    //$('#ujm_exobundle_interactionholetype_holes_'+index+'_wordResponses_0_response').attr("readonly", true);
    $('#ujm_exobundle_interactionholetype_holes_'+index+'_wordResponses_0_score').attr("placeholder", langPoint);
    
    // Remove the useless fileds form
    containerWR.remove();
    
    // Add delete button for hole
    $('#newTable').find('.trHole:last')
                  .append(
                    '<td class="classic"><a id="hole_'+index+'" href="#" class="btn btn-danger">'+langDel+'</a></td>'
                  );

    // When click, delete the matching hole's row in the table
    $('#hole_'+index).click(function(e) {
        var ind = $(this).parent('td').parent('tr').index();
        var val = tinyMCE.get('ujm_exobundle_interactionholetype_html').selection
                         .select(tinyMCE.get('ujm_exobundle_interactionholetype_html').dom.select('.blank')[ind]).value;
        //alert(val);
        tinyMCE.get('ujm_exobundle_interactionholetype_html').selection.setContent(val);
        $(this).parent('td').parent('tr').remove();
        e.preventDefault();
        return false;
    });
    
    $('#add_keyword_'+index).click(function (e) {
            addWR(index);
            e.preventDefault(); // prevent add # in the url
            return false;
        });
    
}

function addWR(index)
{
    addClassVAlign();
    
    //wordResponse
    uniqChoiceID = false;
    indexWR = $('#tabWR_'+index).find('.trWR').length;
    
    while (uniqChoiceID == false) {
        if ($('#ujm_exobundle_interactionholetype_holes_' + index + '_wordResponses_' + indexWR + '_response').length) {
            indexWR++;
        } else {
            uniqChoiceID = true;
        }
    }
    
    $('#tabWR_'+index).find('tbody').append('<tr class="trWR"></tr>');
    
    //alert(containerWR.attr('data-prototype').valueOf());
    containerWR.append(
        $(containerWR.attr('data-prototype')
        .replace(/holes___name__/g, 'holes_'+index)
        .replace(/wordResponses___name__/g, 'wordResponses_'+indexWR)
        .replace(/\[holes\]\[__name__\]/g, '[holes]['+index+']')
        .replace(/\[wordResponses\]\[__name__\]/g, '[wordResponses]['+indexWR+']'))
   );
    /*containerWR.append(
        $(containerWR.attr('data-prototype').replace(/__name__/, 8))
    );*/
        
    containerWR.find('.row').each(function () {
        if ($(this).find('input').length) {
            $('#tabWR_'+index).find('tr:last').append('<td class="classic"></td>');
            $('#tabWR_'+index).find('td:last').append($(this).find('input'));
        }
    });
    
    if (indexWR > 0) {
        $('#tabWR_'+index).find('tr:last').append('<td class="classic"></td>');
        $('#tabWR_'+index).find('td:last').append(
                        '<a id="wr_'+index+'_'+indexWR+'" href="#" class="btn btn-danger">'+langDel+'</a>'
                    );
                        
        // When click, delete the matching keyword's row in the table
        $('#wr_'+index+'_'+indexWR).click(function(e) {
            $(this).parent('td').parent('tr').remove();
            addClassVAlign();
            verticalAlignCenter();
            e.preventDefault();
            return false;
        });
    }
    
    $('#ujm_exobundle_interactionholetype_holes_'+index+'_wordResponses_'+indexWR+'_response').attr("placeholder", langKeyWord);
    $('#ujm_exobundle_interactionholetype_holes_'+index+'_wordResponses_'+indexWR+'_score').attr("placeholder", langPoint);
    
    verticalAlignCenter();
}

function addClassVAlign()
{
    $('#newTable').find('td').each(function () {
            $(this).children('input').addClass('vertical-align-center');
    });
    $('#hole_'+index).addClass('vertical-align-center');
}

function verticalAlignCenter()
{
	$(".vertical-align-center").each(function() {
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
function setOrder() {

    var order = 1;

    $('#newTable').find('.trhole').each(function () {
        $(this).find('input:first').val(order);
        order++;
    });
}