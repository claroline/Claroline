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

function getDoNotDisplayAnymore() {
    console.log('Start checking if we need to display summary...');

    console.log('showSummary = ' + showSummary);
    console.log('typeof isRoot = ' + (typeof isRoot));

    var openSummary = false;

    var doNotDisplayAnymore = window.localStorage.getItem('do-not-display-anymore-' + pathId);
    if (typeof doNotDisplayAnymore !== 'undefined' && null !== doNotDisplayAnymore) {
        if (doNotDisplayAnymore) {
            console.log('User has disabled auto open of summary');
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
                    console.log('Server has said we need to display the summary');
                    openSummary = true;
                    window.localStorage.setItem('do-not-display-anymore-' + pathId, "false");
                } else if (data.isChecked == "true") {
                    console.log('Server has said the auto open of summary is disabled');
                    $('#do-not-display-anymore').prop('checked', true);
                    window.localStorage.setItem('do-not-display-anymore-' + pathId, "true");

                }
            });
        }
    } else if (typeof isRoot != 'undefined' && showSummary) {
        console.log('We are on root and we need to display summary');
        openSummary = true;
    }

    if (openSummary) {
        console.log('DISPLAY SUMMARY');
        $('#full-tree').modal('show');
    }
}