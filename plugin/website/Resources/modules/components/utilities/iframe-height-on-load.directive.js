export default class iframeHeightOnLoadDirective {
  constructor() {
    this.restrict = 'A'
  }

  link($scope, element) {
    element.on('load', () => {
      /* Set the dimensions here,
       I think that you were trying to do something like this: */
      let iFrameHeight = 500
      try {
        iFrameHeight = element[ 0 ].contentWindow.document.body.scrollHeight + 20 + 'px'
      } catch (err) {
        alert('an error occured');
      }
      let iFrameWidth = '100%'
      element.css('width', iFrameWidth)
      element.css('height', iFrameHeight)

      $scope.$evalAsync()
    })
  }
}
