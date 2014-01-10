// div which contain the choices array
var tableHoles = $('#tableHoles'); // div which contain the choices array

// Div which contain the dataprototype
var container = $('div#ujm_exobundle_interactionholetype_holes'); 

//To find the prototype of wordReponse which is integrated in the prototye of hole
tableHoles.append('<div id="prototypes" style="display:none"></div>');
var containerWR = container.attr('data-prototype').valueOf();
$('#prototypes').append(containerWR);
containerWR = $('#ujm_exobundle_interactionholetype_holes___name___wordResponses').attr('data-prototype');

function addFormHole(add, Response, point, size, orthography, del, selector, source_image_add)
{
    tableHoles.append('<table id="newTable" class="table table-striped table-bordered table-condensed"><thead><tr style="background-color: lightsteelblue;"><th class="classic">'+size+'</th><th class="classic">'+orthography+'</th><th class="classic">'+selector+'</th><th class="classic">'+Response+'</th><th class="classic">'+del+'</th></tr></thead><tbody></tbody></table>');
    $('tbody').sortable();
}

function createHole(del)
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
    
    addHole(indexHole - 1, blank, del);
}

function addHole(indHole, valHole, del)
{
    var uniqChoiceID = false;

    var index = $('#newTable').find('tr:not(:first)').length;
    var indexWR = 0;
    
    $('#newTable').find('tbody').append('<tr></tr>');
    
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
    
    $('#ujm_exobundle_interactionholetype_holes_'+index+'_size').val('15');
    
    // Remove the useless fileds form
    container.remove();
    
    $('#newTable').find('tr:last').append('<td class="classic"></td>');
    //alert(containerWR.valueOf());
    containerWR.append(
        $(containerWR.replace(/__name__/, index))
    );
    containerWR.append(
        $(containerWR.replace(/__name__/, 8))
    );
    alert(containerWR.valueOf());
    
    // Add delete button
    $('#newTable').find('tr:last').append('<td class="classic"><a id="hole_'+index+'" href="#" class="btn btn-danger">'+del+'</a></td>');

    // When click, delete the matching choice's row in the table
    $('#hole_'+index).click(function(e) {
        var ind = $(this).parent('td').parent('tr').index();
        var val = tinyMCE.get('ujm_exobundle_interactionholetype_html').selection.select(tinyMCE.get('ujm_exobundle_interactionholetype_html').dom.select('.blank')[ind]).value;
        //alert(val);
        tinyMCE.get('ujm_exobundle_interactionholetype_html').selection.setContent(val);
        $(this).parent('td').parent('tr').remove();
        e.preventDefault();
        return false;
    });
    
}