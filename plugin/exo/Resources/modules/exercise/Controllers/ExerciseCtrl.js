/**
 * Exercise Controller
 * Base controller for Exercises
 */
export default class ExerciseCtrl {
  /**
   * Constructor.
   *
   * @param {ExerciseService}  ExerciseService
   * @param {PaperService}     PaperService
   * @param {UserPaperService} UserPaperService
   * @param {object}           $route
   */
  constructor(ExerciseService, PaperService, UserPaperService, $route) {
    this.ExerciseService  = ExerciseService
    this.PaperService     = PaperService
    this.UserPaperService = UserPaperService

    // Share current Exercise with the whole application
    this.ExerciseService.setExercise(this.exercise)
    this.ExerciseService.setEditEnabled(this.editEnabled)

    if (this.offline) {
      // Disable paper save
      this.PaperService.setNoSaveMode(this.offline)
      // Disable edition
      this.ExerciseService.setEditEnabled(false)
    }

    // Store number of papers already done for the Exercise (all users)
    this.PaperService.setNbPapers(this.nbPapers)

    // Store number of papers already done by the User
    this.UserPaperService.setNbPapers(this.nbUserPapers)

    this.$route = $route
  }

  /**
   * Publish the Current exercise
   */
  publish() {
    this.ExerciseService.publish()
  }

  /**
   * Unpublish the Current exercise
   */
  unpublish() {
    this.ExerciseService.unpublish()
  }
}
