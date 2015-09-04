(function () {
    'use strict';

    angular.module('Sequence').controller('SequencePlayCtrl', [
        'SequenceService',
        function (SequenceService) {

            this.sequence = {};
            this.isCollapsed = false;

            this.setSequence = function (sequence) {
                this.sequence = sequence;
            };

            this.getSequence = function () {
                return this.sequence;
            };
        }
    ]);
})();