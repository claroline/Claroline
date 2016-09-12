import graphic from './../Partials/graphic.html'

export default function GraphicCorrectionCtrl() {
  return {
    restrict: 'E',
    replace: true,
    controller: 'GraphicCorrectionCtrl',
    controllerAs: 'graphicCorrectionCtrl',
    bindToController: true,
    template: graphic,
    scope: {
      question: '=',
      showScore: '='
    }
  }
}
