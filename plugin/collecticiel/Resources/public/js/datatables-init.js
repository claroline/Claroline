$(document).ready(function() {
    $('.datatable').DataTable( {
        "paging":   false,
        "ordering": true,
        "info":     false,
        "searching":false,
        "aaSorting": [],
        "order": [],
        "oLanguage":{"sZeroRecords": "", "sEmptyTable": ""},
        "columnDefs": [ {
          "targets"  : 'no-sort',
          "orderable": false,
        }]
    } );
} );
