
import BaseCriterion from './BaseCriterion'

export default class GroupCriterion extends BaseCriterion {
  constructor($log, $q, $http, Translator, url) {
    super($log, $q, $http, Translator, url)

    this.platformGroups = null
    this.platformGroupsPromise = null
    this.userGroups = null
    this.userGroupsPromise = null
  }

  /**
   * Test the criterion.
   *
   * @param {object} step - the step being checked
   * @param {string} dataToTest - contains the ID of the group the User must belong
   */
  test(step, dataToTest) {
    const deferred = this.$q.defer()

    this.$q
      .all([
        this.getPlatformGroups(),
        this.getUserGroups()
      ])
      .then(() => {
        const errors = []
        if (!this.userGroups[dataToTest]) {
          // The current User is not a member of the criterion group
          let message = null
          if (0 === this.userGroups.length) {
            // Current user has no group
            message = this.Translator.trans('condition_criterion_test_usergroup_nogroup', {activityGroup: this.platformGroups[dataToTest]}, 'path_wizards')
          } else {
            message = this.Translator.trans('condition_criterion_test_usergroup', {activityGroup: this.platformGroups[dataToTest], userGroup: this.getGroupNames(this.userGroups)}, 'path_wizards')
          }

          errors.push(message)
        }

        deferred.resolve(errors)
      })

    return deferred.promise
  }

  getGroupNames(groups) {
    let groupNames = ''
    for (let groupId in groups) {
      if (groups.hasOwnProperty(groupId)) {
        groupNames += (0 === groupNames.length ? groups[groupId] : ', ' + groups[groupId])
      }
    }

    return groupNames
  }

  /**
   * Get the list of groups defined in the platform
   */
  getPlatformGroups() {
    if (!this.platformGroupsPromise) { // Avoid duplicate call if the first one is not finished
      const deferred = this.$q.defer()

      if (null !== this.platformGroups) {
        deferred.resolve(this.platformGroups)
      } else {
        this.$http
          .get(this.UrlGenerator('innova_path_criteria_groups'))
          .success((response) => {
            this.platformGroups = response
            deferred.resolve(response)
            delete this.platformGroupsPromise
          })
          .error((response) => {
            deferred.reject(response)
            delete this.platformGroupsPromise
          })

        this.platformGroupsPromise = deferred.promise
      }

      return deferred.promise
    }

    return this.platformGroupsPromise
  }

  /**
   * Get the list of groups of the current User
   */
  getUserGroups() {
    if (!this.userGroupsPromise) {
      const deferred = this.$q.defer()

      if (null !== this.userGroups) {
        deferred.resolve(this.userGroups)
      } else {
        this.$http
          .get(this.UrlGenerator('innova_path_criteria_user_groups'))
          .success((response) => {
            this.userGroups = response
            deferred.resolve(response)
            delete this.userGroupsPromise
          })
          .error((response) => {
            deferred.reject(response)
            delete this.userGroupsPromise
          })

        this.userGroupsPromise = deferred.promise
      }

      return deferred.promise
    }

    return this.userGroupsPromise
  }
}
