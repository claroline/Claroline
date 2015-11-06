/**
 * AuthorizationChecker Service
 */
(function () {
    'use strict';

    angular.module('StepConditionsModule').factory('AuthorizationCheckerService', [
        'AlertService',
        'PathService',
        'UserProgressionService',
        'StepConditionsService',
        function AuthorizationCheckerService(AlertService, PathService, UserProgressionService, StepConditionsService) {

            return {
                isAuthorized: function isAuthorized(step) {
                    var authorized = false;

                    var currentStepId = step.id;
                    var rootStep = PathService.getRoot();

                    var userProgression = UserProgressionService.get();

                    //make sure root is accessible anyways
                    if (typeof userProgression[rootStep.id] == 'undefined'
                        || !angular.isDefined(userProgression[rootStep.id].authorized)
                        || !userProgression[rootStep.id].authorized) {
                        UserProgressionService.update(rootStep, userProgression[rootStep.id].status, 1);    //pass 1 (and not "true") to controller : problem in url
                    }
                    //previous step exists ? NO : we're on root step => access
                    if (!angular.isObject(PathService.getPrevious(step))) {
                        authorized = true;
                        //previous step exists ? YES
                    } else {
                        var previousstep = PathService.getPrevious(step);
                        //is there a flag authorized on current step ? YES => access
                        if (typeof userProgression[currentStepId] !== 'undefined'
                            && angular.isDefined(userProgression[currentStepId].authorized)
                            && userProgression[currentStepId].authorized) {
                            authorized = true;
                            //is there a flag authorized on current step ? NO (or because the progression is not set)
                        } else {
                            //activity has been set for the step : NO => path error
                            if (!angular.isDefined(previousstep.activityId)) {
                                AlertService.addAlert('error', Translator.trans('step_access_denied_no_activity_set', {stepName: step.name}, 'path_wizards'));
                                //activity has been set for the step : YES
                            } else {
                                //is there a flag authorized on previous step ? YES
                                if (typeof userProgression[previousstep.id] !== 'undefined'
                                    && angular.isDefined(userProgression[previousstep.id].authorized)
                                    && userProgression[previousstep.id].authorized) {
                                    //retrieve user progression
                                    var progression = userProgression[step.id];
                                    var status = (typeof progression == 'undefined') ? "seen" : progression.status;
                                    //is there a condition on previous step ? YES
                                    if (angular.isDefined(previousstep.condition) && angular.isObject(previousstep.condition)) {
                                        //get the promise
                                        var activityEvaluationPromise = StepConditionsService.getActivityEvaluation(previousstep.activityId);
                                        activityEvaluationPromise.then(
                                            function (result) {
                                                // validate condition on previous step ? YES
                                                if (StepConditionsService.testCondition(previousstep, result)) {
                                                    //add flag to current step
                                                    var promise = UserProgressionService.update(step, status, 1);
                                                    promise.then(function(result){
                                                        //grant access
                                                        PathService.goTo(step);
                                                    }.bind(this));          //important, to keep the scope
                                                    // validate condition on previous step ? NO
                                                } else {
                                                    var conditionlist = StepConditionsService.getConditionList();
                                                    //display error
                                                    AlertService.addAlert('error', Translator.trans('step_access_denied_condition', {stepName: step.name, conditionList: conditionlist}, 'path_wizards'));
                                                }
                                            });
                                        //is there a condition on previous step ? NO
                                    } else {
                                        //add flag to current step
                                        var promise = UserProgressionService.update(step, status, 1);
                                        promise.then(function(result){
                                            //grant access
                                            authorized = true;
                                        });
                                    }
                                    //is there a flag authorized on previous step ? NO => no access => message
                                } else {
                                    //display error
                                    AlertService.addAlert('error', Translator.trans('step_access_denied', {}, 'path_wizards'));
                                }
                            }
                        }
                    }

                    return authorized;
                }
            };
        }
    ]);
})();