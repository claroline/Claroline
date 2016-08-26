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
        '$q',
        function AuthorizationCheckerService(AlertService, PathService, UserProgressionService, StepConditionsService, $q) {

            var maybeAuthorizedStep=[];

            return {
                /**
                 * Check if the UserProgression grant access to the Step
                 * @param step
                 * @returns {boolean}
                 */
                isProgressionAuthorized: function isProgressionAuthorized(step) {
                    var progressionAuthorized = false;
                    var userProgression = UserProgressionService.get();
                    if (angular.isDefined(userProgression[step.id]) && angular.isDefined(userProgression[step.id].authorized) && userProgression[step.id].authorized) {
                        // Step is already authorized
                        progressionAuthorized = true;
                    }
                    return progressionAuthorized;
                },

                doAuthorizeSteps:function doAuthorizeSteps(){
                    //loop through steps to authorize if needed
                    for(var i=0;i<maybeAuthorizedStep.length;i++){
                        var progression=UserProgressionService.getForStep(maybeAuthorizedStep[i]);
                        var status=(typeof progression=='undefined'||null===progression)?"seen":progression.status;
                        UserProgressionService.update(maybeAuthorizedStep[i],status,1);
                    }
                    //go to step
                    if(maybeAuthorizedStep.length>0){
                        PathService.goTo(maybeAuthorizedStep[0]);
                    }
                    //reset array
                    maybeAuthorizedStep.length=0;
                },

                checkStepConditions: function checkStepConditions(step, previous, authorization, that) {
                     var progression = UserProgressionService.getForStep(step);
                     var status = (typeof progression == 'undefined' || null === progression) ? "seen" : progression.status;
                         // There is a condition on the previous step => check if it is OK
                         // Process evaluation of the Activity
                         StepConditionsService.getActivityEvaluation(previous.activityId).then(function onSuccess(result) {
                             if (StepConditionsService.testCondition(previous, result)) {
                                 //doesn't apply to step and previous (< N-1)
                                 that.isAuthorized(previous, authorization);

                             } else {
                                 // validate condition on previous step ? NO
                                 var conditionsList = StepConditionsService.getConditionList();
                                 //display error
                                 authorization.resolve({
                                     granted: false,
                                     message: Translator.trans('step_access_denied_condition', {stepName: step.name, conditionList: conditionsList}, 'path_wizards')
                                 });
                                 maybeAuthorizedStep.length=0;
                             }
                         });
                         //si pas de conditions sur S-1 (qui est déjà auth) => OK
                 },
                /**
                 * Check if the User can access to the Step
                 * @param step
                 * @returns {object}
                 */
                isAuthorized: function isAuthorized(step, authorization, arr) {
                    if (!PathService.getEditEnabled()) {
                        if (PathService.isCompleteBlockingCondition()) {
                            return this.isAuthorizedAllSteps(step, authorization, arr);
                        } else {
                            return this.isAuthorizedNextStep(step, authorization, arr);
                        }
                    } else {
                        if (!authorization) {
                            authorization = $q.defer();
                        }
                        authorization.resolve({granted: true});
                        var progression=UserProgressionService.getForStep(step);
                        var status=(typeof progression=='undefined'||null===progression)?"seen":progression.status;
                        //Enables
                        UserProgressionService.update(step,'seen',1);
                        PathService.goTo(step);
                    }
                },
                /**
                 * Case when a step is blocking all next steps
                 * @returns {object} promise
                 */
                isAuthorizedAllSteps: function isAuthorizedAllSteps(step, authorization, arr) {
                    // Authorization object (contains a granted boolean and a message if not authorized)
                    if (!authorization) {
                        authorization = $q.defer();
                    }

                    if (angular.isDefined(arr))
                        maybeAuthorizedStep = arr;

                    if (PathService.getRoot() == step) {
                        // Always grant access to the Root step
                        authorization.resolve({granted: true});
                        this.doAuthorizeSteps();
                        // previous step exists ? YES
                    } else {
                        // Check authorization for the current step
                        var previous = PathService.getPrevious(step);

                        // Check progression of the User to know if the step is already granted
                        if (this.isProgressionAuthorized(step)) {
                            //doesn't apply to step and previous (< N-1)
                            if (maybeAuthorizedStep.length > 0) {
                                maybeAuthorizedStep.push(step);
                                this.isAuthorized(previous, authorization);
                            } else {
                                // Step has already been marked as accessible => grant access
                                authorization.resolve({granted: true});
                                this.doAuthorizeSteps();
                            }
                        } else {
                            maybeAuthorizedStep.push(step);
                            // Step is not already granted => so check conditions
                            if (!angular.isDefined(previous.activityId)) {
                                // activity has been set for the step : NO => path error
                                authorization.resolve({
                                    granted: false,
                                    message: Translator.trans('step_access_denied_no_activity_set', {stepName: step.name}, 'path_wizards')
                                });
                                maybeAuthorizedStep.length=0;
                            } else {
                                // retrieve user progression
                                var progression = UserProgressionService.getForStep(step);
                                var status = (typeof progression == 'undefined' || null === progression) ? "seen" : progression.status;

                                //activity has been set for the step : YES
                                if (this.isProgressionAuthorized(previous)) {
                                    // Previous step is authorized, so check the condition to know if User can access current step
                                    if (angular.isDefined(previous.condition) && angular.isObject(previous.condition)) {
                                        // There is a condition on the previous step => check if it is OK
                                        this.checkStepConditions(step, previous, authorization, this);
                                    } else {
                                        // Don't stand by for request response => authorize access anyway
                                        authorization.resolve({ granted: true });
                                        this.doAuthorizeSteps();
                                    }
                                } else {
//because we can't just test Root for condition : even unauthorized step should be tested for condition
// The previous step is not accessible, so the current step neither
                                    if (angular.isDefined(previous.condition) && angular.isObject(previous.condition)) {
                                        this.checkStepConditions(step, previous, authorization, this);
                                    } else {
                                        if (PathService.getRoot() == previous) {
                                            authorization.resolve({ granted: true });
                                            this.doAuthorizeSteps();
                                        } else {
                                            this.isAuthorized(previous, authorization);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    return authorization.promise;
                },
                /**
                 * Case when a step is blocking only the step S + 1
                 */

                isAuthorizedNextStep: function isAuthorizedNextStep(step, authorization, arr) {
                    // Authorization object (contains a granted boolean and a message if not authorized)
                    var authorization = $q.defer();

                    if (PathService.getRoot() == step) {
                        // Always grant access to the Root step
                        authorization.resolve({granted: true});
                        // previous step exists ? YES
                    } else {
                        // Check authorization for the current step
                        var previous = PathService.getPrevious(step);

                        // Check progression of the User to know if the step is already granted
                        if (this.isProgressionAuthorized(step)) {
                            // Step has already been marked as accessible => grant access
                            authorization.resolve({granted: true});
                        } else {
                            // Step is not already granted => so check conditions
                            if (!angular.isDefined(previous.activityId)) {
                                // activity has been set for the step : NO => path error
                                authorization.resolve({
                                    granted: false,
                                    message: Translator.trans('step_access_denied_no_activity_set', {stepName: step.name}, 'path_wizards')
                                });
                            } else {
                                // Previous step is authorized, so check the condition to know if User can access current step
                                // retrieve user progression
                                var progression = UserProgressionService.getForStep(step);
                                var status = (typeof progression == 'undefined' || null === progression) ? "seen" : progression.status;

                                if (angular.isDefined(previous.condition) && angular.isObject(previous.condition)) {
                                    // There is a condition on the previous step => check if it is OK

                                    // Process evaluation of the Activity
                                    StepConditionsService.getActivityEvaluation(previous.activityId).then(function onSuccess(result) {
                                        if (StepConditionsService.testCondition(previous, result)) {
                                            // validate condition on previous step ? YES
                                            // Update UserProgression
                                            UserProgressionService.update(step, status, 1);

                                            authorization.resolve({ granted: true });
                                        } else {
                                            // validate condition on previous step ? NO
                                            var conditionsList = StepConditionsService.getConditionList();
                                            //display error
                                            authorization.resolve({
                                                granted: false,
                                                message: Translator.trans('step_access_denied_condition', {stepName: step.name, conditionList: conditionsList}, 'path_wizards')
                                            });
                                        }
                                    });
                                } else {
                                    // Previous step doesn't lock the current step so access it and update the User progression
                                    UserProgressionService.update(step, status, 1);

                                    // Don't stand by for request response => authorize access anyway
                                    authorization.resolve({ granted: true });
                                }
                            }
                        }
                    }
                    return authorization.promise;
                }
            };
        }
    ]);
})();
