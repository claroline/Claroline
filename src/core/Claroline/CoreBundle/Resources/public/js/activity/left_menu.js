(function(){
    var currentStep = 1;
    $('#step-'+1).css('font-weight', 'bold');
    var totalSteps = document.getElementById('twig-attributes').getAttribute('data-total-steps');

    $('#progress-bar').html(currentStep+'/'+totalSteps);

    $('.icon-arrow-right').live('click', function(){
        currentStep++;
        loadActivity(currentStep);
    });

    $('.icon-arrow-left').live('click', function(){
        currentStep--;
        loadActivity(currentStep);
    });

    loadActivity = function(step){
        currentStep = step;
        $('#progress-bar').html(currentStep+'/'+totalSteps);
        var resourceId = document.getElementById('twig-attributes').getAttribute('data-step-'+currentStep+'-resource-id');
        var resourceType = document.getElementById('twig-attributes').getAttribute('data-step-'+currentStep+'-resource-type');
        $('#step-'+step).css('font-weight', 'bold');
        window.parent.document.getElementById('right-frame').src = Routing.generate('claro_resource_open', {'resourceId': resourceId, 'resourceType': resourceType});
    }
})();



