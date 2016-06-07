import timer from './../Partials/timer.html'

/**
 * Create a Timer
 * @param {TimerService} TimerService
 * @constructor
 */
function TimerDirective(TimerService) {
    return {
        restrict: 'E',
        replace: true,
        template: timer,
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
}

export default TimerDirective
