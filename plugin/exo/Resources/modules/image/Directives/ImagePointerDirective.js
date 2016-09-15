import angular from 'angular/index'

import template from './../Partials/img-pointer.html'
import pointer from './../pointer.png'

/**
 * A draggable pointer
 * (we use jQuery to have access to the normalized .pageX, .pageY for mouse position)
 *
 * @constructor
 */
function ImagePointerDirective($window, $document) {
  return {
    restrict: 'E',
    replace: true,
    template: template,
    require: 'ngModel',
    scope: {
      img: '=',
      disabled: '='
    },
    link: function link(scope, element, attr, ngModel) {
      /**
       * Defines the pointer shape
       * @type {{url: string, size: number}}
       */
      scope.pointer = {
        img: pointer,
        size: 16
      }

      /**
       * Transform model value to view value
       * We have x, y coords (of the center) and we need to transform them to a top, left position
       * @param   {Object} modelValue
       * @returns {Object}
       */
      var format = function format(modelValue) {
        if (modelValue) {
          // Calculate position (based on the current img size for responsiveness)
          var originalHeight = scope.img.data('original-height')
          var originalWidth = scope.img.data('original-width')
          var current = scope.img.get(0).getBoundingClientRect()

          return {
            left: modelValue.x * (current.width / originalWidth),
            top:  modelValue.y * (current.height / originalHeight)
          }
        }

        return modelValue
      }

      /**
       * Transform view value into model value
       * We have a top, left position and we need to transform them to x, y coords (of the center)
       * @param   {Object} viewValue
       * @returns {Object}
       */
      var parse = function parse(viewValue) {
        if (viewValue) {
          // Calculate position (based on the current img size for responsiveness)
          var originalHeight = scope.img.data('original-height')
          var originalWidth = scope.img.data('original-width')
          var current = scope.img.get(0).getBoundingClientRect()

          return {
            x: viewValue.left * (originalWidth / current.width),
            y: viewValue.top * (originalHeight / current.height)
          }
        }

        return viewValue
      }

      /**
       * EVENT : Start dragging the pointer
       * @param event
       */
      var dragPointer = function dragPointer(event) {
        $document.on('mousemove', movePointer)
        $document.on('mouseup', dropPointer)

        event.preventDefault()
      }

      /**
       * EVENT : Move the pointer on the image
       * @param event
       */
      var movePointer = function movePointer(event) {
        var rect = scope.img.get(0).getBoundingClientRect()

        var top = event.pageY - rect.top - $window.pageYOffset
        var left = event.pageX - rect.left - $window.pageXOffset

        // Lock the pointer into the image
        var coords = parse({top: top, left: left})
        if (coords.x > 0 && coords.x < rect.width
          && coords.y > 0 && coords.y < rect.height) {
          // The pointer is still on the image, we can update its position
          scope.top  = top
          scope.left = left

          // Manually override position to make the cursor move
          // The scope changes will be propagated at the end of the drag for performance reasons
          element.get(0).style.top = scope.top + 'px'
          element.get(0).style.left = scope.left + 'px'
        }
      }

      /**
       * EVENT : Drop the pointer
       * @param event
       */
      var dropPointer = function dropPointer(event) {
        // Notify angular values have changed
        scope.$apply(function () {
          ngModel.$setViewValue({
            top: scope.top,
            left: scope.left
          })
        })

        // Destroy drag n drop events
        $document.off('mousemove', movePointer)
        $document.off('mouseup', dropPointer)

        event.preventDefault()
      }

      /**
       * Recalculate the pointer position based on the img size and model value
       */
      var recalculatePosition = function recalculatePosition() {
        ngModel.$setViewValue(format(ngModel.$modelValue))
      }

      // Configure ngModel
      ngModel.$formatters.push(format)
      ngModel.$parsers.push(parse)
      ngModel.$render = function() {
        scope.top  = ngModel.$viewValue.top
        scope.left = ngModel.$viewValue.left
      }

      scope.img.on('load', recalculatePosition)
      angular.element($window).on('resize', recalculatePosition)

      scope.$watch('disabled', function (newValue) {
        if (newValue) {
          element.off('mousedown', dragPointer)
        } else {
          element.on('mousedown', dragPointer)
        }
      })

      scope.$on('$destroy', function () {
        element.off('mousedown', dragPointer)
        scope.img.off('load', recalculatePosition)
        angular.element($window).off('resize', recalculatePosition)
      })
    }
  }
}

export default ImagePointerDirective
