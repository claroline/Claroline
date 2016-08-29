
import ActivityCriterion from './ActivityCriterion'

export default class ActivityStatusCriterion extends ActivityCriterion {
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

      const status = evaluation.status ? evaluation.status : 'N/A'
      if (status !== dataToTest) {
        // The activity has not the correct status
        errors.push(this.Translator.trans('condition_criterion_test_status', {activityStatus: dataToTest, userStatus: status}, 'path_wizards'))
      }

      deferred.resolve(errors)
    })

    return deferred.promise
  }
}
