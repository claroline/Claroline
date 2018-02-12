angular
  .module("pageslide-directive", [])
  .directive('pageslide', [
    function () {
      var defaults = {};

      /* Return directive definition object */

      return {
        restrict: "EAC",
        transclude: false,
        scope: {
          psOpen: "=?",
          psAutoClose: "=?",
          psSide: "@",
          psSpeed: "@",
          psClass: "@",
          psSize: "@",
          psSqueeze: "@",
          psCloak: "@",
          psPush: "=?",
          psPushClass: "@",
          psPinClass: "@"
        },
        //template: '<div class="pageslide-content" ng-transclude></div>',
        link: function ($scope, el, attrs) {
          $scope.psPush = $scope.psPush || false;
          /* Parameters */
          var param = {};

          param.side = $scope.psSide || 'right';
          param.speed = $scope.psSpeed || '0.5';
          param.size = $scope.psSize || '300px';
          param.zindex = 1000; // Override with custom CSS
          param.className = $scope.psClass || 'ng-pageslide';
          param.cloak = $scope.psCloak && $scope.psCloak.toLowerCase() == 'false' ? false : true;
          param.squeeze = Boolean($scope.psSqueeze) || false;
          param.pushClass = $scope.psPushClass || 'ng-pageslide-container';
          param.pinClass = $scope.psPinClass || 'ng-pageslide-pinned';

          // Apply Class
          el.addClass(param.className);

          /* DOM manipulation */
          var content = null;
          var slider = null;
          var body = document.body;

          slider = el[0];

          // Check for div tag
          if (slider.tagName.toLowerCase() !== 'div' &&
            slider.tagName.toLowerCase() !== 'pageslide')
            throw new Error('Pageslide can only be applied to <div> or <pageslide> elements');

          // Check for content
          if (slider.children.length === 0)
            throw new Error('You have to content inside the <pageslide>');

          content = angular.element(slider.children);

          /* Append */
          body.appendChild(slider);

          /* Style setup */
          slider.style.zIndex = param.zindex;
          slider.style.position = 'fixed'; // this is fixed because has to cover full page
          slider.style.width = 0;
          slider.style.height = 0;
          slider.style.overflow = 'hidden';
          slider.style.transitionDuration = param.speed + 's';
          slider.style.webkitTransitionDuration = param.speed + 's';
          slider.style.transitionProperty = 'width, height';
          if (param.squeeze) {
            body.style.position = 'absolute';
            body.style.transitionDuration = param.speed + 's';
            body.style.webkitTransitionDuration = param.speed + 's';
            body.style.transitionProperty = 'top, bottom, left, right';
          }

          switch (param.side){
            case 'right':
              slider.style.height = attrs.psCustomHeight || '100%';
              slider.style.top = attrs.psCustomTop ||  '0px';
              slider.style.bottom = attrs.psCustomBottom ||  '0px';
              slider.style.right = attrs.psCustomRight ||  '0px';
              break;
            case 'left':
              slider.style.height = attrs.psCustomHeight || '100%';
              slider.style.top = attrs.psCustomTop || '0px';
              slider.style.bottom = attrs.psCustomBottom || '0px';
              slider.style.left = attrs.psCustomLeft || '0px';
              break;
            case 'top':
              slider.style.width = attrs.psCustomWidth || '100%';
              slider.style.left = attrs.psCustomLeft || '0px';
              slider.style.top = attrs.psCustomTop || '0px';
              slider.style.right = attrs.psCustomRight || '0px';
              break;
            case 'bottom':
              slider.style.width = attrs.psCustomWidth || '100%';
              slider.style.bottom = attrs.psCustomBottom || '0px';
              slider.style.left = attrs.psCustomLeft || '0px';
              slider.style.right = attrs.psCustomRight || '0px';
              break;
          }


          /* Closed */
          function psClose(slider,param){
            if (slider && slider.style.width !== 0 && slider.style.width !== 0){
              if (param.cloak) content.css('display', 'none');
              switch (param.side){
                case 'right':
                  slider.style.width = '0px';
                  if (param.squeeze) body.style.right = '0px';
                  if ($scope.psPush) {
                    body.style.right = '0px';
                    body.style.left = '0px';
                  }

                  break;
                case 'left':
                  slider.style.width = '0px';
                  if (param.squeeze) body.style.left = '0px';
                  if ($scope.psPush) {
                    body.style.left = '0px';
                    body.style.right = '0px';
                  }
                  break;
                case 'top':
                  slider.style.height = '0px';
                  if (param.squeeze) body.style.top = '0px';
                  if ($scope.psPush) {
                    body.style.top = '0px';
                    body.style.bottom = '0px';
                  }
                  break;
                case 'bottom':
                  slider.style.height = '0px';
                  if (param.squeeze) body.style.bottom = '0px';
                  if ($scope.psPush) {
                    body.style.bottom = '0px';
                    body.style.top = '0px';
                  }
                  break;
              }

              body.className = body.className.replace(param.pushClass,'');
            }
            $scope.psOpen = false;
          }

          /* Open */
          function psOpen(slider, param){
            if (slider.style.width !== 0 && slider.style.width !== 0){
              switch (param.side){
                case 'right':
                  slider.style.width = param.size;
                  if (param.squeeze) body.style.right = param.size;
                  if ($scope.psPush) {
                    body.style.right = param.size;
                    body.style.left = "-" + param.size;
                  }
                  break;
                case 'left':
                  slider.style.width = param.size;
                  if (param.squeeze) body.style.left = param.size;
                  if ($scope.psPush) {
                    body.style.left = param.size;
                    body.style.right = "-" + param.size;
                  }
                  break;
                case 'top':
                  slider.style.height = param.size;
                  if (param.squeeze) body.style.top = param.size;
                  if ($scope.psPush) {
                    body.style.top = param.size;
                    body.style.bottom = "-" + param.size;
                  }
                  break;
                case 'bottom':
                  slider.style.height = param.size;
                  if (param.squeeze) body.style.bottom = param.size;
                  if ($scope.psPush) {
                    body.style.bottom = param.size;
                    body.style.top = "-" + param.size;
                  }
                  break;
              }

              body.className = body.className.trim() + ' ' + param.pushClass;

              setTimeout(function(){
                if (param.cloak) content.css('display', 'block');
              },(param.speed * 1000));

            }
          }

          /* Enable Push */
          function psEnablePush(slider, param) {
            if (slider && slider.style.width !== 0 && slider.style.width !== 0) {
              if ($scope.psOpen) {
                switch (param.side){
                  case 'right':
                    body.style.right = param.size;
                    body.style.left = "-" + param.size;
                    break;
                  case 'left':
                    body.style.left = param.size;
                    body.style.right = "-" + param.size;
                    break;
                  case 'top':
                    body.style.top = param.size;
                    body.style.bottom = "-" + param.size;
                    break;
                  case 'bottom':
                    body.style.bottom = param.size;
                    body.style.top = "-" + param.size;
                    break;
                }
              }
              if (body.className.indexOf(param.pinClass) == -1) {
                body.className = body.className.trim() + ' ' + param.pinClass;
              }
            }
          }

          /* Disable push */
          function psDisablePush(slider, param) {
            if (slider && slider.style.width !== 0 && slider.style.width !== 0) {
              if ($scope.psOpen) {
                switch (param.side){
                  case 'right':
                  case 'left':
                    body.style.right = '0px';
                    body.style.left = '0px';
                    break;
                  case 'top':
                  case 'bottom':
                    body.style.top = '0px';
                    body.style.bottom = '0px';
                    break;
                }
              }

              body.className = (body.className.replace(param.pinClass,'')).trim();
            }
          }

          function isFunction(functionToCheck){
            var getType = {};
            return functionToCheck && getType.toString.call(functionToCheck) === '[object Function]';
          }

          /*
           * Watchers
           * */

          $scope.$watch("psOpen", function (value){
            if (!!value) {
              // Open
              psOpen(slider,param);
            } else {
              // Close
              psClose(slider,param);
            }
          });

          $scope.$watch("psPush", function (value){
            if (!!value) {
              // Enable
              psEnablePush(slider,param);
            } else {
              // Disable
              psDisablePush(slider,param);
            }
          });


          /*
           * Events
           * */

          $scope.$on('$destroy', function() {
            document.body.removeChild(slider);
          });

          if($scope.psAutoClose){
            $scope.$on("$locationChangeStart", function(){
              psClose(slider, param);
            });
            $scope.$on("$stateChangeStart", function(){
              psClose(slider, param);
            });

          }
        }
      };
    }
  ]);
