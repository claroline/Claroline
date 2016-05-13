var OpenCorrectionDirective = function OpenCorrectionDirective() {
    return {
        restrict: 'E',
        replace: true,
        controller: 'OpenCorrectionCtrl',
        controllerAs: 'openCorrectionCtrl',
        bindToController: true,
        templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Correction/Partials/open.html',
        scope: {
            question: '='
        }
    };
};

// Set up dependency injection
OpenCorrectionDirective.$inject = [];

// Register directive into AngularJS
angular
    .module('Correction')
    .directive('openCorrection', OpenCorrectionDirective);


