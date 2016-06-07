var GraphicCorrectionCtrl = function GraphicCorrectionCtrl($timeout) {
    return {
        restrict: 'E',
        replace: true,
        controller: 'GraphicCorrectionCtrl',
        controllerAs: 'graphicCorrectionCtrl',
        bindToController: true,
        templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Correction/Partials/graphic.html',
        scope: {
            question: '='
        }
    };
};

// Set up dependency injection
GraphicCorrectionCtrl.$inject = [ '$timeout' ];

// Register directive into AngularJS
angular
    .module('Correction')
    .directive('graphicCorrection', GraphicCorrectionCtrl);
