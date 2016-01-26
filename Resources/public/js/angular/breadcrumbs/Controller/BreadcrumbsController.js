//extension avec des plugins ?
//ça me semble compliqué pour une SPA.

var controller = function() {
    this.breadcrumbs = [];
}

angular.module('ClarolineBreadcrumbs').controller('ClarolineBreadcrumbsController', [controller]);