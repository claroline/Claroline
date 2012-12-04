(function(){
var currentStep = 1;
var totalSteps = document.getElementById('twig-attributes').getAttribute('data-total-steps');

$('#progress-bar').html(currentStep+'/'+totalSteps);

$('.icon-arrow-right').live('click', function(){
    currentStep++;
    $('#progress-bar').html(currentStep+'/'+totalSteps);

    var resourceId = document.getElementById('twig-attributes').getAttribute('data-step-'+currentStep+'-resource-id');
    var resourceType = document.getElementById('twig-attributes').getAttribute('data-step-'+currentStep+'-resource-type');
    window.parent.document.getElementById('right-frame').src = Routing.generate('claro_resource_open', {'resourceId': resourceId, 'resourceType': resourceType});
});

$('.icon-arrow-left').live('click', function(){
    currentStep--;
    $('#progress-bar').html(currentStep+'/'+totalSteps);

    var resourceId = document.getElementById('twig-attributes').getAttribute('data-step-'+currentStep+'-resource-id');
    var resourceType = document.getElementById('twig-attributes').getAttribute('data-step-'+currentStep+'-resource-type');
    window.parent.document.getElementById('right-frame').src = Routing.generate('claro_resource_open', {'resourceId': resourceId, 'resourceType': resourceType});
});
})();