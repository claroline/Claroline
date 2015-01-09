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
        dataType: 'json'
    })
    .done(function(data) {
        window.localStorage.setItem('do-not-display-anymore-' + pathId, isChecked);
    });
}

function getDoNotDisplayAnymore(){
    var openSummary = false;

    var doNotDisplayAnymore = window.localStorage.getItem('do-not-display-anymore-' + pathId);
    if (typeof doNotDisplayAnymore !== 'undefined' && null !== doNotDisplayAnymore) {
        if (doNotDisplayAnymore) {
            $('#do-not-display-anymore').prop('checked', true);
            openSummary = false;
        } else {
            $.ajax({
                url: Routing.generate('getDoNotDisplayAnymore'),
                type: 'GET',
                data:{
                    pathId: pathId
                },
                dataType: 'json'
            })
            .done(function(data) {
                if (typeof isRoot != 'undefined' && data.isChecked == false && showSummary) {
                    openSummary = true;
                    window.localStorage.setItem('do-not-display-anymore-' + pathId, "false");
                } else if (data.isChecked == "true") {
                    $('#do-not-display-anymore').prop('checked', true);
                    window.localStorage.setItem('do-not-display-anymore-' + pathId, "true");

                }
            });
        }
    } else if (typeof isRoot != 'undefined' && showSummary) {
        openSummary = true;
    }

    if (openSummary) {
        $('#full-tree').modal('show');
    }
}