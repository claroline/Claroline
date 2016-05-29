var ChoiceCorrectionDirective = function ChoiceCorrectionDirective() {
    return {
        restrict: 'E',
        replace: true,
        controller: 'ChoiceCorrectionCtrl',
        controllerAs: 'choiceCorrectionCtrl',
        bindToController: true,
        templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Correction/Partials/choice.html',
        scope: {
            question: '='
        }
    };
};

// Set up dependency injection
ChoiceCorrectionDirective.$inject = [];

// Register directive into AngularJS
angular
    .module('Correction')
    .directive('choiceCorrection', ChoiceCorrectionDirective);


