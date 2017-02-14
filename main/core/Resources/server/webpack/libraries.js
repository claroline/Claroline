/**
 * Exports lists of modules to be bundled as external "dynamic" libraries
 *
 * (@see webpack DllPlugin and DllReferencePlugin)
 */
module.exports = {
  'angular_dll': [
    'angular',
    'angular/angular.min',
    'angular-animate',
    'angular-bootstrap',
    'angular-bootstrap-colorpicker',
    'angular-breadcrumb',
    'angular-daterangepicker',
    'angular-datetime',
    'angular-data-table/release/dataTable.helpers.min',
    'angular-dragula',
    'angular-loading-bar',
    'angular-resource',
    'angular-route',
    'angular-sanitize',
    'angular-strap',
    'angular-toArrayFilter',
    'angular-touch',
    'angular-ui-router',
    'angular-ui-select',
    'angular-ui-tinymce',
    'angular-ui-translation',
    'angular-ui-tree',
    'angular-ui-pageslide',
    'ng-file-upload',
    'at-table/dist/angular-table',
    'angular-dragula'
  ],
  'es6_dll': ['core-js', 'whatwg-fetch']
}
