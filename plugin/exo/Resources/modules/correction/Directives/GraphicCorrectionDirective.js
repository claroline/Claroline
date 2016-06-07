function GraphicCorrectionCtrl() {
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
