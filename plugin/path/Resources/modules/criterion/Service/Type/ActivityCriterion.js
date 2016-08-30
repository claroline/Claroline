
import BaseCriterion from './BaseCriterion'

export default class ActivityCriterion extends BaseCriterion {
  constructor($log, $q, $http, Translator, url) {
    super($log, $q, $http, Translator, url)

    this.statuses = null
    this.statusesPromise = null
  }

  getStatuses() {
    if (!this.statusesPromise) { // Avoid duplicate call if the first one is not finished
      const deferred = this.$q.defer()

      if (null !== this.statuses) {
        deferred.resolve(this.statuses)
      } else {
        this.$http
          .get(this.UrlGenerator('innova_path_criteria_activity_statuses'))
          .success((response) => {
            this.statuses = response
            deferred.resolve(response)
            delete this.statusesPromise
          })
          .error((response) => {
            deferred.reject(response)
            delete this.statusesPromise
          })

        this.statusesPromise = deferred.promise
      }

      return deferred.promise
    }

    return this.statusesPromise
  }

  /**
   * Retrieve the user evaluation for a step activity
   *
   * @param step
   */
  getEvaluation(step) {
    const deferred = this.$q.defer()

    this.$http
      .get(this.UrlGenerator('innova_path_criteria_evaluation', {id: step.activityId}))
      .success((response) => {
        deferred.resolve(response)
      })
      .error((response) => {
        deferred.reject(response)
      })

    return deferred.promise
  }
}
