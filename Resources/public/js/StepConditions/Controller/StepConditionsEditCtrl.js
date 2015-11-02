/**
 * Class constructor
 * @returns {StepConditionsEditCtrl}
 * @constructor
 */
    //StepService
var StepConditionsEditCtrl = function StepConditionsEditCtrl($route, $routeParams, PathService, StepConditionsService, $scope, ConfirmService, IdentifierService) {
    StepConditionsBaseCtrl.apply(this, arguments);

    // Inject service
    this.scope                  = $scope;
    this.confirmService         = ConfirmService;
    this.identifierService      = IdentifierService;
    this.stepConditionsService  = StepConditionsService;
    this.pathService            = PathService;

    //list of all groups a user s registered to
    this.useringroup = this.pathService.getUseringroupData();

    //default values for conditions
    //values for user group list
    this.criterionUsergroup = this.pathService.getUsergroupData();
    //values for user team list
    this.criterionUserteam = this.pathService.getUserteamData();

    //values for activity statuses (here got from AJAX request, +(maybe hardcode the list in partial for better performance...)
    this.criterionActivitystatuses=this.pathService.getEvaluationStatusesData();

    this.criterionActivitystatus = 'passed';
    this.criterionActivityrepetition = 1;
    this.criterion = {};
    this.criterion.type = 'activitystatus';

    return this;
};

// Extends the base controller
StepConditionsEditCtrl.prototype = Object.create(StepConditionsBaseCtrl.prototype);
StepConditionsEditCtrl.prototype.constructor = StepConditionsEditCtrl;

// Show action buttons for a step in the tree (contains the ID of the step)
StepConditionsEditCtrl.prototype.showButtons = null;

/**
 * Create a new condition for a given step
 * @param step
 */
StepConditionsEditCtrl.prototype.createCondition = function (stepId) {
    var step = this.pathService.getStep(stepId);
    this.conditionstructure = [];
    this.conditionstructure.push(this.stepConditionsService.initialize(step));
};

/**
 * Delete a condition
 */
StepConditionsEditCtrl.prototype.deleteCondition = function(stepId) {
    var step = this.pathService.getStep(stepId);
    this.confirmService.open(
        // Confirm options
        {
            title:         Translator.trans('condition_delete_title',   {}, 'path_wizards'),
            message:       Translator.trans('condition_delete_confirm', {}, 'path_wizards'),
            confirmButton: Translator.trans('condition_delete',         {}, 'path_wizards')
        },

        // Confirm success callback
        function () {
            //remove the condition (needs to be step.condition to trigger change and allow path save)
            step.condition = null;
            this.conditionstructure = [];
        }.bind(this)
    );
};

/**
 * Adds a criteria group to the condition
 * @param criteriagroup
 */
StepConditionsEditCtrl.prototype.addCriteriagroup = function(criteriagroup) {
    //use the service method to add a new criteriagroup
    this.stepConditionsService.addCriteriagroup(criteriagroup);
};

/**
 * Adds a criterion to the condition
 */
StepConditionsEditCtrl.prototype.addCriterion = function(criteriagroup) {
    //use the service method to add a new criterion
    this.stepConditionsService.addCriterion(criteriagroup);
};

/**
 * Delete a criteria group (and its children)
 */
StepConditionsEditCtrl.prototype.removeCriteriagroup = function(group) {
    this.confirmService.open(
        // Confirm options
        {
            title:         Translator.trans('criteriagroup_delete_title',   {}, 'path_wizards'),
            message:       Translator.trans('criteriagroup_delete_confirm', {}, 'path_wizards'),
            confirmButton: Translator.trans('criteriagroup_delete',         {}, 'path_wizards')
        },

        // Confirm success callback
        function () {
            //use the service method to add a remove a criteriagroup
            this.stepConditionsService.removeCriteriagroup(this.conditionstructure[0].criteriagroups, group);
        }.bind(this)
    );
};

/**
 * Delete a criterion
 */
StepConditionsEditCtrl.prototype.removeCriterion = function(group, index) {
    this.confirmService.open(
        // Confirm options
        {
            title:         Translator.trans('criterion_delete_title',   {}, 'path_wizards'),
            message:       Translator.trans('criterion_delete_confirm', {}, 'path_wizards'),
            confirmButton: Translator.trans('criterion_delete',         {}, 'path_wizards')
        },

        // Confirm success callback
        function () {
            //remove the criterion
            group.criterion.splice(index, 1);
        }.bind(this)
    );
};