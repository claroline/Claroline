/**
 * Class constructor
 * @returns {StepShowCtrl}
 * @constructor
 */
var StepShowCtrl = function StepShowCtrl(step, inheritedResources, PathService, $sce, UserProgressionService, $filter, StepConditionsService, AlertService) {
    StepBaseCtrl.apply(this, arguments);

    this.userProgressionService = UserProgressionService;
    this.userProgression = this.userProgressionService.get();
    this.stepConditionsService = StepConditionsService;
    this.alertService = AlertService;

    this.filterDate = $filter('date');

    if (angular.isDefined(this.step) && angular.isDefined(this.step.description) && typeof this.step.description == 'string') {
        // Trust content to allow Cross Sites URL
        this.step.description = $sce.trustAsHtml(this.step.description);
    }

    // Update User progression if needed (e.g. if the User has never seen the Step, mark it as seen)
    this.progression = this.userProgressionService.getForStep(this.step);
    if (!angular.isObject(this.progression)) {
        //root step is authorized anyways
        var authorized = (this.pathService.getRoot().id == step.id) ? 1 : 0;
        // Create progression for User
        this.progression = this.userProgressionService.create(step, null, authorized);
    }

    return this;
};

// Extends the base controller
StepShowCtrl.prototype = Object.create(StepBaseCtrl.prototype);
StepShowCtrl.prototype.constructor = StepShowCtrl;

/**
 * Service that manages the User Progression in the Path
 * @type {{}}
 */
StepShowCtrl.prototype.userProgressionService = {};

StepShowCtrl.prototype.filterDate = {};

/**
 * Progression of the User for the current Step (NOT the progression for the whole Path)
 * @type {null}
 */
StepShowCtrl.prototype.progression = {};

StepShowCtrl.prototype.isAccessible = function () {
    var now = this.filterDate(new Date(), 'yyyy-MM-dd HH:mm:ss')

    var from = null;
    if (this.step.accessibleFrom != null && this.step.accessibleFrom.length !== 0) {
        from = this.step.accessibleFrom;
    }

    var until = null;
    if (this.step.accessibleUntil != null && this.step.accessibleUntil.length !== 0) {
        until = this.step.accessibleUntil;
    }

    var accessible = false;
    if ( (null === from || now >= from) && (null === until || now <= until) ) {
        accessible = true;
    }

    return accessible;
};

StepShowCtrl.prototype.updateProgression = function (newStatus) {
    this.userProgressionService.update(this.step, newStatus);
};

StepShowCtrl.prototype.updateProgression = function (newStatus) {
    this.userProgressionService.update(this.step, newStatus);
};

StepShowCtrl.prototype.goTo = function goTo(step) {

    var curentStepId = step.id;
    var rootStep = this.pathService.getRoot();

    //make sure root is accessible anyways
    if (typeof this.userProgression[rootStep.id]=='undefined'
        || !angular.isDefined(this.userProgression[rootStep.id].authorized)
        || !this.userProgression[rootStep.id].authorized) {
        this.userProgressionService.update(rootStep, this.userProgression[rootStep.id].status, 1);    //pass 1 (and not "true") to controller : problem in url
    }
    //previous step exists ? NO : we're on root step => access
    if (!angular.isObject(this.pathService.getPrevious(step))) {
        this.pathService.goTo(step);
        //previous step exists ? YES
    } else {
        var previousstep = this.pathService.getPrevious(step);
        //is there a flag authorized on current step ? YES => access
        if (typeof this.userProgression[curentStepId] !== 'undefined'
            && angular.isDefined(this.userProgression[curentStepId].authorized)
            && this.userProgression[curentStepId].authorized) {
            this.pathService.goTo(step);
            //is there a flag authorized on current step ? NO (or because the progression is not set)
        } else {
            //activity has been set for the step : NO => path error
            if (!angular.isDefined(previousstep.activityId)) {
                this.alertService.addAlert('error', Translator.trans('step_access_denied_no_activity_set', {stepName: step.name}, 'path_wizards'));
                //activity has been set for the step : YES
            } else {
                //is there a flag authorized on previous step ? YES
                if (typeof this.userProgression[previousstep.id] !== 'undefined'
                    && angular.isDefined(this.userProgression[previousstep.id].authorized)
                    && this.userProgression[previousstep.id].authorized) {
                    //retrieve user progression
                    var progression = this.userProgression[step.id];
                    var status = (typeof progression == 'undefined') ? "seen" : progression.status;
                    //is there a condition on previous step ? YES
                    if (angular.isDefined(previousstep.condition) && angular.isObject(previousstep.condition)) {
                        //get the promise
                        var activityEvaluationPromise = this.stepConditionsService.getActivityEvaluation(previousstep.activityId);
                        activityEvaluationPromise.then(
                            function (result) {
                                this.evaluation = result;
                                // validate condition on previous step ? YES
                                if (this.stepConditionsService.testCondition(previousstep, this.evaluation)) {
                                    //add flag to current step
                                    var promise = this.userProgressionService.update(step, status, 1);
                                    promise.then(function(result){
                                        //grant access
                                        this.pathService.goTo(step);
                                    }.bind(this));          //important, to keep the scope
                                    // validate condition on previous step ? NO
                                } else {
                                    var conditionlist=this.stepConditionsService.getConditionList();
                                    //display error
                                    this.alertService.addAlert('error', Translator.trans('step_access_denied_condition', {stepName: step.name, conditionList: conditionlist}, 'path_wizards'));
                                }
                            }.bind(this),
                            function (error) {
                                this.evaluation = null;
                            }.bind(this));
                        //is there a condition on previous step ? NO
                    } else {
                        //add flag to current step
                        var promise = this.userProgressionService.update(step, status, 1);
                        promise.then(function(result){
                            //grant access
                            this.pathService.goTo(step);
                        }.bind(this));
                    }
                    //is there a flag authorized on previous step ? NO => no access => message
                } else {
                    //display error
                    this.alertService.addAlert('error', Translator.trans('step_access_denied', {}, 'path_wizards'));
                }
            }
        }
    }
};
