/**
 * Class constructor
 * @returns {PathSummaryEditCtrl}
 * @constructor
 */
var PathSummaryEditCtrl = function PathSummaryEditCtrl($routeParams, PathService, ClipboardService, ConfirmService, IdentifierService) {
    PathSummaryBaseCtrl.apply(this, arguments);

    // Inject service
    this.identifierService = IdentifierService;
    this.clipboardService  = ClipboardService;
    this.confirmService    = ConfirmService;

    // Initialize some value
    this.clipboardDisabled = this.clipboardService.getDisabled();
    this.maxDepth = this.pathService.getMaxDepth();

    this.treeOptions = {
        dragStart: function (event) {
            // Disable tooltip on drag handlers
            $('.angular-ui-tree-handle').tooltip('disable');

            // Hide tooltip for the dragged element
            if (event.source && event.source.nodeScope && event.source.nodeScope.$element) {
                event.source.nodeScope.$element.find('.angular-ui-tree-handle').tooltip('toggle');
            }
        },
        dropped: function (event) {
            // Enable tooltip on drag handlers
            $('.angular-ui-tree-handle').tooltip('enable');

            // Recalculate step levels
            this.pathService.reorderSteps(this.structure);
        }.bind(this)
    };

};

// Extends the base controller
PathSummaryEditCtrl.prototype = Object.create(PathSummaryBaseCtrl.prototype);
PathSummaryEditCtrl.prototype.constructor = PathSummaryEditCtrl;

/**
 * Current state of the clipboard
 * @type {object}
 */
PathSummaryEditCtrl.prototype.clipboardDisabled = null;

// Show action buttons for a step in the tree (contains the ID of the step)
PathSummaryEditCtrl.prototype.showButtons = null;

/**
 * Maximum depth of the Path
 * @type {number}
 */
PathSummaryEditCtrl.prototype.maxDepth = null;

/**
 * Summary sortable options
 * @type {object}
 */
PathSummaryEditCtrl.prototype.treeOptions = {};

/**
 * Initialize an empty structure for path
 */
PathSummaryEditCtrl.prototype.createNew = function () {
    this.pathService.initialize();
};

/**
 * Initialize the structure from a selected template
 */
PathSummaryEditCtrl.prototype.createFromTemplate = function () {
    // Open select modal

    // Get the root of the template as current step
};

/**
 * Add a new step child to specified step
 */
PathSummaryEditCtrl.prototype.addStep = function (parentStep) {
    this.pathService.addStep(parentStep, true);
};

/**
 * Copy step into clipboard
 */
PathSummaryEditCtrl.prototype.copy = function (step) {
    this.clipboardService.copy(step);
};

/**
 * Paste clipboard content
 */
PathSummaryEditCtrl.prototype.paste = function (step) {
    // Paste clipboard content into children of the step
    this.clipboardService.paste(step.children, function (clipboardData) {
        // Change step IDs before paste them
        this.pathService.browseSteps([ clipboardData ], function (parentStep, step) {
            step.id = this.identifierService.generateUUID();

            // Reset server step ID
            step.resourceId = null;

            // Reset Activity ID to generate a new one when publishing path
            step.activityId = null;

            // Override name
            step.name  = step.name ? step.name + ' ' : '';
            step.name += '(' + Translator.trans('copy', {}, 'path_wizards') + ')';
        }.bind(this));
    }.bind(this));

    // Recalculate step levels
    this.pathService.reorderSteps(this.structure);
};

/**
 * Remove a step from Tree
 */
PathSummaryEditCtrl.prototype.removeStep = function (step) {
    this.confirmService.open(
        // Confirm options
        {
            title:         Translator.trans('step_delete_title',   { stepName: step.name }, 'path_wizards'),
            message:       Translator.trans('step_delete_confirm', {}                     , 'path_wizards'),
            confirmButton: Translator.trans('step_delete',         {}                     , 'path_wizards')
        },

        // Confirm success callback
        function () {
            // Check if we are deleting the current editing step
            var updatePreview = false;
            if (step.id === this.current.stepId) {
                // Need to update preview
                updatePreview = true;
            }
            // Effective remove
            this.pathService.removeStep(this.structure, step);

            // Update current editing step
            if (updatePreview) {
                if (this.structure[0]) {
                    // Display root step
                    this.goTo(this.structure[0]);
                } else {
                    // There is no longer steps into the path => hide step form
                    this.goTo(null);
                }
            }
        }.bind(this)
    );
};
