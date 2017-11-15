import angular from 'angular/index'

/* global Routing */

angular.module('ui.badgePicker', [])
    .directive('uiBadgePicker', function () {
      var url = Routing.generate('icap_badge_badge_picker')
      var data = {
        mode: 'user'
      }

      return {
        restrict: 'A',
        link: function ($scope, element, attrs) {
          var customData = {}
          var successCallback = function () {
            return null
          }
          if (attrs.uiBadgePicker) {
            var badgepickerConfig = $scope.$eval(attrs.uiBadgePicker)
            customData     = badgepickerConfig.data || {}
            successCallback = badgepickerConfig.successCallback || successCallback
          }
          angular.extend(data, customData)

          var closeCallback = function (nodes) {
            var newSelectedValue = []
            angular.forEach(nodes, function (element) {
              newSelectedValue.push(element.id)
            })

            this.data.value = newSelectedValue
          }

                // Initialize badge picker object
          window.Claroline.BadgePicker.configureBadgePicker(url, data, successCallback, closeCallback)

          $scope.badgePickerOpen = function () {
            window.Claroline.BadgePicker.openBadgePicker()
          }

          element[0].onclick = function (event) {
            event.preventDefault()
            $scope.badgePickerOpen()
          }
        }
      }
    })
