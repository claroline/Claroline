import open from './../Partials/open.html'

export default function OpenCorrectionDirective() {
    return {
        restrict: 'E',
        replace: true,
        controller: 'OpenCorrectionCtrl',
        controllerAs: 'openCorrectionCtrl',
        bindToController: true,
        template: open,
        scope: {
            question: '='
        }
    };
}
