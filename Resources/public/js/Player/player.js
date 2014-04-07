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
            isChecked: isChecked,
            pathId: pathId
        },
        dataType: 'json',
    })
    .done(function(data) {
        window.localStorage.setItem('do-not-display-anymore-' + pathId, isChecked);
    });
}

function getDoNotDisplayAnymore(){
    var doNotDisplayAnymore = window.localStorage.getItem('do-not-display-anymore-' + pathId);

    if (doNotDisplayAnymore == "true"){
        $('#do-not-display-anymore').prop('checked', true);
    } else if (doNotDisplayAnymore != "false") {
        $.ajax({
            url: Routing.generate('getDoNotDisplayAnymore'),
            type: 'GET',
            data:{
                pathId: pathId
            },
            dataType: 'json',
        })
        .done(function(data) {
            if (typeof isRoot != 'undefined' && data.isChecked == false) {
                $('#full-tree').modal('show');
                window.localStorage.setItem('do-not-display-anymore-' + pathId, "false");
            } else if (data.isChecked == "true") {
                $('#do-not-display-anymore').prop('checked', true);
                window.localStorage.setItem('do-not-display-anymore-' + pathId, "true");

            }
        });
    } else if (typeof isRoot != 'undefined') {
        $('#full-tree').modal('show');
    }
}