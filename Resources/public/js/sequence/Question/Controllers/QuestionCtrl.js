(function () {
    'use strict';

    angular.module('Question').controller('QuestionCtrl', [  
        '$modalInstance',
        'questions',
        function ($modalInstance, questions) {
            /**
             * List of all questions available
             */
            this.questions = questions;
            /**
             * Send back selected question and close modal
             */
            this.select = function (selected) {
                $modalInstance.close(selected);
            };

            /**
             * Abort type selection
             */
            this.cancel = function () {
                $modalInstance.dismiss('cancel');
            };
        }
    ]);
})();