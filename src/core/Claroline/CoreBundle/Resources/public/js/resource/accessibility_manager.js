$(function(){
    $('#switch_mode').val('spiral');
    $('#submit_select').click(function() {
        window.location = Routing.generate(
        'claro_resource_accessibility_form', {'resourceType':$('#select_type').val(), 'parentId':document.getElementById('claro_data').getAttribute('data-parent_id')}
        );
    });

    $('#switch_mode').change(function() {
        window.location = Routing.generate('claro_dashboard_resources', {'mode': $('#switch_mode').val()});
    })
});
