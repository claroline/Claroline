/**
 * Page Service
 */
(function () {
    'use strict';
    angular.module('Common').factory('CommonService', [
        '$http',
        '$filter',
        '$q',
        function StepService($http, $filter, $q) {

            this.sequence = {};
            this.currentQuestion = {};
            this.currentAnswer = {};
            this.currentPenalty = 0;

            return {
                /**
                 * Set the current sequence
                 * Used for share data between directives
                 * @param {type} sequence
                 * @returns {undefined}
                 */
                setSequence: function (sequence) {
                    this.sequence = sequence;
                },
                getSequence: function () {
                    return this.sequence;
                },
                getSequenceMeta: function () {
                    return this.sequence.meta;
                },
                /**
                 * @param {object} object a javascript object with type property
                 * @returns null or string
                 */
                getObjectSimpleType: function (object) {
                    if (!object.type || object.type === undefined || object.type === 'undefined') {
                        return null;
                    } else {
                        var simpleType = null;
                        if (object.type === 'text/html') {
                            simpleType = 'html-text';
                        }
                        else if (object.type === 'text/plain') {
                            simpleType = 'simple-text';
                        }
                        else if (object.type === 'application/pdf' && object.url) {
                            simpleType = 'web-pdf';
                        }
                        else if ((object.type === 'image/png' || object.type === 'image/jpg' || object.type === 'image/jpeg') && object.url) {
                            simpleType = 'web-image';
                        }
                        else if ((object.type === 'image/png' || object.type === 'image/jpg' || object.type === 'image/jpeg') && object.encoding && object.data) {
                            simpleType = 'encoded-image';
                        }

                        return simpleType;
                    }
                },
                /**
                 * @param {object} object a javascript object with meta property
                 * @returns null or string
                 */
                objectHasOtherMeta: function (object) {
                    if (!object.meta || object.meta === undefined || object.meta === 'undefined') {
                        return null;
                    }
                    return object.meta.licence || object.meta.created || object.meta.modified || (object.meta.description && object.meta.description !== '');
                },
                setCurrentQuestionAndAnswer: function (answer, question, penalty) {
                    this.currentPenalty = penalty;
                    this.currentAnswer = answer;
                    this.currentQuestion = question;
                },
                getStudentData: function () {
                    return{
                        penalty: this.currentPenalty,
                        answer: this.currentAnswer,
                        question: this.currentQuestion
                    };
                }
            };
        }
    ]);
})();