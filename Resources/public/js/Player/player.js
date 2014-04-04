$( document ).ready(function() {
	$("*").tooltip({placement:'top'});

    getDoNotDisplayAnymore();

    $('#current-step-text a').attr('target','_blank');

    $('#do-not-display-anymore').change(function() {
        var isChecked = $('#do-not-display-anymore').is(':checked');
        setDoNotDisplayAnymore(isChecked);
    });
   
});


function setDoNotDisplayAnymore(isChecked){
    $.ajax({
        url: Routing.generate('setDoNotDisplayAnymore'),
        type: 'GET',
        data:{
            isChecked: isChecked
        },
        dataType: 'json',
    });
}

function getDoNotDisplayAnymore(isChecked){
    $.ajax({
        url: Routing.generate('getDoNotDisplayAnymore'),
        type: 'GET',
        dataType: 'json',
    })
    .done(function(data) {
        if (typeof isRoot != 'undefined' && data.isChecked == "false") {
            $('#full-tree').modal('show');
        } else if (data.isChecked == "true") {
            $('#do-not-display-anymore').prop('checked', true);
        }
    });    
}