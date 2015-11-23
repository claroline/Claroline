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
                        progressionAuthorized = true;
                    }

                    return progressionAuthorized;
                },

                /**
                 * Check if the User can access to the Step
                 * @param step
                 * @returns {object}
                 */
                isAuthorized: function isAuthorized(step) {
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
                                //activity has been set for the step : YES
                                if (this.isProgressionAuthorized(previous)) {
                                    // Previous step is authorized, so check the condition to know if User can access current step
                                    // retrieve user progression
                                    var progression = UserProgressionService.getForStep(step);
                                    var status = (typeof progression == 'undefined' || null == progression) ? "seen" : progression.status;

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
                                } else {
                                    // The previous step is not accessible, so the current step neither
                                    authorization.resolve({
                                        granted: false,
                                        message: Translator.trans('step_access_denied', {}, 'path_wizards')
                                    });
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