export default class iframeHeightOnLoadDirective {
  constructor() {
    this.restrict = 'A'
  }

  link($scope, element) {
    element.on('load', () => {
      /* Set the dimensions here,
       I think that you were trying to do something like this: */
      this.resizeElement(element, $scope)
      // For the next 5seconds run setInterval, in case there is js content loaded
      let cnt = 0
      let intv = setInterval(() => {
        this.resizeElement(element, $scope)
        cnt++
        if (cnt === 10) {
          clearInterval(intv)
        }
      }, 500)
    })
  }

  resizeElement(element, $scope) {
    let iFrameHeight = '100vh'
    try {
      iFrameHeight = element[ 0 ].contentWindow.document.body.scrollHeight + 'px'
    } catch (err) {
      alert('an error occured')
    }
    let iFrameWidth = '100%'
    element.css('width', iFrameWidth)
    element.css('height', iFrameHeight)
    element.css('display', 'block')
    element.css('border', 'none')
    $scope.$evalAsync()
  }
}
