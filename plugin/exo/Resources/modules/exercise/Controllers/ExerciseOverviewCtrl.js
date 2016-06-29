/**
 * Display the general information about the Exercise
 *
 * @param {ExerciseService}  ExerciseService
 * @param {UserPaperService} UserPaperService
 * @constructor
 */
function ExerciseOverviewCtrl(ExerciseService, UserPaperService) {
    this.ExerciseService = ExerciseService;
    this.UserPaperService = UserPaperService;

    this.exercise    = this.ExerciseService.getExercise();
    this.editEnabled = this.ExerciseService.isEditEnabled();
};

/**
 * Current Exercise
 * @type {Object}
 */
ExerciseOverviewCtrl.prototype.exercise = null;

/**
 * If the current User has the rights to administrate the Exercise,
 * we display him tools to do it
 * @type {boolean}
 */
ExerciseOverviewCtrl.prototype.editEnabled = false;

/**
 * Display/Hide additional info of the Exercise
 * @type {boolean}
 */
ExerciseOverviewCtrl.prototype.additionalInfo = false;

export default ExerciseOverviewCtrl
