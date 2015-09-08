(function () {
    'use strict';

    angular.module('Sequence').controller('SequencePlayCtrl', [
        'SequenceService',
        'CommonService',
        function (SequenceService, CommonService) {

            this.sequence = {};
            this.setSequence = function (sequence) {
                this.sequence = CommonService.setSequence(sequence);
            };

            this.getSequence = function () {
                return CommonService.getSequence();
            };

            /**
             * Check if the question has meta like created / licence / description...
             */
            this.questionHasOtherMeta = function () {
                
                return CommonService.objectHasOtherMeta(this.sequence);
                //console.log(this.sequence.meta);
                //return this.sequence.meta.licence || this.sequence.meta.created || this.sequence.meta.modified || (this.sequence.meta.description && this.sequence.meta.description != '');
            };
        }
    ]);
})();