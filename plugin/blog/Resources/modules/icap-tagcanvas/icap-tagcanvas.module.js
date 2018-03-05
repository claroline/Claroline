import angular from 'angular/index'
import $ from 'jquery'
import '#/main/core/jquery/tag-canvas/jquery.tagcanvas.min.js'

angular
  .module('icap.tagcanvas', [])
  .directive('tagcanvas', () => {
    return {
      restrict: 'A',
      link: (scope, elem) => {
        $(document).ready(function () {
          $(elem).tagcanvas({
            textColour : '#428BCA',
            outlineThickness : 1,
            maxSpeed : 0.05,
            textHeight: 18,
            depth : 0.5,
            weight : true,
            outlineColour : '#2A6496',
            outlineMethod : 'colour',
            reverse : true
          }, 'tagList')
        })
      }
    }
  })
