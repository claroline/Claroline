$(function(){
    $('#submit_select').click(function() {
        window.location = Routing.generate(
        'claro_resource_accessibility_form', {'resourceType':$('#select_type').val(), 'parentId':document.getElementById('claro_data').getAttribute('data-parent_id')}
        );
    });

    $('#switch_mode').change(function() {
        window.location = Routing.generate('claro_dashboard_resources');
        //Cookie generation here.
    })
});
