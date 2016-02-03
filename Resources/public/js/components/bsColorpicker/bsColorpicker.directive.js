(function(){
    'use strict';

    angular.module('bs.colorpicker').
    directive('bsColorpicker', bsColorpicker);

    function bsColorpicker() {
        var directive = {
            restrict: 'EA',
            scope:{
                'color' : "=color"
            },
            template:   '<input type="text" class="form-control" value="{{vm.color}}" id="bg-color"/>'+
            '<span class="input-group-addon"><i></i></span>'+
            '<span data-ng-click="vm.clearColor($event)" class="input-group-addon transparent-color-btn"><i data-ng-click="vm.clearColor($event)" class="fa fa-ban"></i></span>',
            link: link,
            controller: ColorpickerController,
            controllerAs: 'vm',
            bindToController: true
        };

        return directive;

        ///////////////////////////////////////////////////////////
        function link($scope, $element, attrs, controller) {
            var options = {
                customClass: 'ws-colorpicker',
                format: 'hex',
                color: controller.color
            };
            var colorpicker = $element.colorpicker(options);

            return colorpicker.on('changeColor.colorpicker', controller.onColorpickerChange);
        }
    }

    ColorpickerController.$inject = ['$scope', '$element'];
    function ColorpickerController($scope, $element) {
        var vm = this;
        vm.color                = (vm.color || 'transparent').toLowerCase();
        vm.clearColor           = clearColor;
        vm.onColorpickerChange  = onColorpickerChange;

        ////////////////////////////////////////////////////
        function changeColor(colorHex) {
            vm.color = colorHex;
            $scope.$evalAsync();
        }

        function clearColor(event) {
            event.preventDefault();
            event.stopPropagation();

            setColorpickerColor('transparent');

            return false;
        }

        function onColorpickerChange(event) {
            changeColor(event.color.toHex());
        }

        function setColorpickerColor(color) {
            $element.colorpicker('setValue', color);
        }
    }
})();