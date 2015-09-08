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
            this.steps = {};
            this.currentStep = {};
            return {
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
                /**
                 * set the sequence
                 * @param {object} sequence
                 */
                setSequence: function (sequence) {
                    this.sequence = sequence;
                    return this.sequence;
                },
                /**
                 * get the sequence
                 * @returns {object}
                 */
                getSequence: function () {
                    return this.sequence;
                },
                setSteps: function () {
                    this.steps = this.sequence.steps;
                    return this.steps;
                },
                getSteps:function(){
                    return this.steps;
                },
                /**
                 * set the current step
                 * @param {object} step
                 */
                setCurrentStep: function (step) {
                    this.currentStep = step;
                    return this.currentStep;
                },
                /**
                 * get the current step
                 * @returns {object}
                 */
                getCurrentStep: function () {
                    return this.currentStep;
                },
                validateStep: function (choices) {
                    // check if answers are ok
                    // check other conditions (when conditional sequence will be on)
                    // return points ?
                }
            };
        }
    ]);
})();