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
            
             /**
             * Check if the question has meta like created / licence, description...
             * @returns {boolean}
             */
            this.questionHasOtherMeta = function () {
                console.log(this.sequence.meta);
                return this.sequence.meta.licence || this.sequence.meta.created || this.sequence.meta.modified || (this.sequence.meta.description && this.sequence.meta.description != '');
            };
        }
    ]);
})();