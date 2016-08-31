
import ActivityCriterion from './ActivityCriterion'

export default class ActivityAttemptCriterion extends ActivityCriterion {
  /**
   * Test the criterion.
   *
   * @param {object} step - the step being checked
   * @param {string} dataToTest - contains the needed status for the activity
   */
  test(step, dataToTest) {
    const deferred = this.$q.defer()

    this.getEvaluation(step).then((evaluation) => {
      const errors = []

      const attempts = evaluation.attempts ? parseInt(evaluation.attempts) : 0
      if (attempts < parseInt(dataToTest)) {
        // Expected number of attempts not reached

        errors.push(this.Translator.trans('condition_criterion_test_repetition', {activityRep: dataToTest, userRep: attempts}, 'path_wizards'))
      }

      deferred.resolve(errors)
    })

    return deferred.promise
  }
}