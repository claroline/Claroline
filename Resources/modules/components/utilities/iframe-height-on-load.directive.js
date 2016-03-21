export default function iframeHeightOnLoadDirective() {
  var directive = {
    restrict: 'A',
    link: link
  };

  return directive;
  ////////////////////////////

  function link(scope, element) {
    element.on('load', function () {
      /* Set the dimensions here,
       I think that you were trying to do something like this: */
      var iFrameHeight = 500;
      try {
        iFrameHeight = element[ 0 ].contentWindow.document.body.scrollHeight + 20 + 'px';
      } catch (err) {
      }
      var iFrameWidth = '100%';
      element.css('width', iFrameWidth);
      element.css('height', iFrameHeight);

      scope.$apply();
    });
  }
}