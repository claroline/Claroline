/**
 * Create a Timer
 * @param {TimerService} TimerService
 * @constructor
 */
var TimerDirective = function TimerDirective(TimerService) {
    return {
        restrict: 'E',
        replace: true,
        templateUrl: AngularApp.webDir + 'bundles/ujmexo/js/angular/Common/Partials/timer.html',
        bindToController: true,
        controllerAs: 'timerCtrl',
        controller: function TimerCtrl() {},
        link: function link(scope, element, attrs, ctrl) {
            if (ctrl.timerStart) {
                TimerService.start(ctrl.timer.id);
            }

            if (ctrl.timerDestroy) {
                scope.$on('$destroy', function onDestroy() {
                    TimerService.destroy(ctrl.timer.id);
                });
            }
        },
        scope: {
            /**
             * The Timer to display
             */
            timer: '=',

            /**
             * Start the Timer when the Directive is created ?
             */
            timerStart: '=',

            /**
             * End the Timer when the Directive is destroyed ?
             */
            timerDestroy: '='
        }
    };
};

// Set up dependency injection
TimerDirective.$inject = [ 'TimerService' ];

// Register directive into AngularJS
angular
    .module('Common')
    .directive('timer', TimerDirective);