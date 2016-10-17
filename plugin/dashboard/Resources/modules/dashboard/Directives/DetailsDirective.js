import details from './../Partials/details.html'

export default function DetailsDirective() {
  return {
    restrict: 'E',
    replace: true,
    template: details,
    scope: {
      computed: '=',
      dashboard: '=',
      user: '='
    }
  }
}
