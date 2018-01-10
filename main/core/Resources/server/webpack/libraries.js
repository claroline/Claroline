/**
 * Exports lists of modules to be bundled as external "dynamic" libraries
 *
 * (@see webpack DllPlugin and DllReferencePlugin)
 */
module.exports = {
  'react_dll': [
    'react',
    'redux',
    'react-dom',
    'react-redux',
    'react-router-dom',
    'reselect',
    'prop-types',
    'invariant',
    'classnames'
  ],
  'angular_dll': [
    'angular',
    'angular-animate',
    'angular-ui-bootstrap',
    'angular-bootstrap-colorpicker',
    'angular-breadcrumb',
    'angular-daterangepicker',
    'angular-datetime-input',
    'angular-loading-bar',
    'angular-resource',
    'angular-route',
    'angular-sanitize',
    'angular-strap',
    'angular-toarrayfilter',
    'angular-touch',
    'angular-ui-router',
    'ui-select',
    'angular-ui-tinymce',
    'angular-ui-translation',
    'angular-ui-tree',
    'angular-ui-pageslide',
    'ng-file-upload'
  ]
}
