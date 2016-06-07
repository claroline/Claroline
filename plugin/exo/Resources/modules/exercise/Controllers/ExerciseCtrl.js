/**
 * Exercise Controller
 * Base controller for Exercises
 * @param {ExerciseService}  ExerciseService
 * @param {PaperService}     PaperService
 * @param {UserPaperService} UserPaperService
 * @param {object}           $route
 * @constructor
 */
function ExerciseCtrl(ExerciseService, PaperService, UserPaperService, $route) {
    this.ExerciseService  = ExerciseService;
    this.PaperService     = PaperService;
    this.UserPaperService = UserPaperService;

    // Share current Exercise with the whole application
    this.ExerciseService.setExercise(this.exercise);
    this.ExerciseService.setEditEnabled(this.editEnabled);

    // Store number of papers already done for the Exercise (all users)
    this.PaperService.setNbPapers(this.nbPapers);

    // Store number of papers already done by the User
    this.UserPaperService.setNbPapers(this.nbUserPapers);

    this.$route = $route;
};

/**
 * Current Exercise
 * @type {Object}
 */
ExerciseCtrl.prototype.exercise = null;

/**
 * The numbers of Papers submitted for this Exercise
 * @type {Number}
 */
ExerciseCtrl.prototype.nbPapers = 0;

/**
 * The numbers of Papers submitted for this Exercise
 * @type {Number}
 */
ExerciseCtrl.prototype.nbUserPapers = 0;

/**
 * If the current User has the rights to administrate the Exercise,
 * we display him tools to do it
 * @type {boolean}
 */
ExerciseCtrl.prototype.editEnabled = false;

/**
 * Publish the Current exercise
 */
ExerciseCtrl.prototype.publish = function publish() {
    this.ExerciseService.publish();
};

/**
 * Unpublish the Current exercise
 */
ExerciseCtrl.prototype.unpublish = function unpublish() {
    this.ExerciseService.unpublish();
};

export default ExerciseCtrl
