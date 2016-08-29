
import BaseCriterion from './BaseCriterion'

export default class TeamCriterion extends BaseCriterion {
  constructor($log, $q, $http, Translator, url, PathService) {
    super($log, $q, $http, Translator, url)

    // We need the current path to be able to retrieve the workspace
    this.PathService = PathService

    this.workspaceTeams = null
    this.workspaceTeamsPromise = null
    this.userTeams = null
    this.userTeamsPromise = null
  }

  /**
   * Test the criterion.
   *
   * @param {object} step - the step being checked
   * @param {string} dataToTest - the ID of the team the User must belong
   */
  test(step, dataToTest) {
    const deferred = this.$q.defer()

    this.$q
      .all([
        this.getWorkspaceTeams(),
        this.getUserTeams()
      ])
      .then(() => {
        const errors = []
        if (!this.userTeams[dataToTest]) {
          // The current User is not a member of the criterion team
          let message = null
          if (0 === this.userTeams.length) {
            // Current user has no team
            message = this.Translator.trans('condition_criterion_test_userteam_noteam', {activityTeam: this.workspaceTeams[dataToTest]}, 'path_wizards')
          } else {
            message = this.Translator.trans('condition_criterion_test_userteam', {activityTeam: this.workspaceTeams[dataToTest], userTeam: this.getTeamNames(this.userTeams)}, 'path_wizards')
          }

          errors.push(message)
        }

        deferred.resolve(errors)
      })

    return deferred.promise
  }

  getTeamNames(teams) {
    let teamNames = ''
    for (let groupId in teams) {
      if (teams.hasOwnProperty(groupId)) {
        teamNames += (0 === teamNames.length ? teams[groupId] : ', ' + teams[groupId])
      }
    }

    return teamNames
  }

  /**
   * Get the list of teams available in the Workspace
   */
  getWorkspaceTeams() {
    if (!this.workspaceTeamsPromise) {
      const deferred = this.$q.defer()

      if (null !== this.workspaceTeams) {
        deferred.resolve(this.workspaceTeams)
      } else {
        this.$http
          .get(this.UrlGenerator('innova_path_criteria_teams', { id: this.PathService.getId() }))
          .success((response) => {
            this.workspaceTeams = response

            deferred.resolve(response)
            delete this.workspaceTeamsPromise
          })
          .error((response) => {
            deferred.reject(response)
            delete this.workspaceTeamsPromise
          })

        this.workspaceTeamsPromise = deferred.promise
      }

      return deferred.promise
    }

    return this.workspaceTeamsPromise
  }

  /**
   * Get the list of teams of the current User
   */
  getUserTeams() {
    if (!this.userTeamsPromise) { // Avoid duplicate call if the first one is not finished
      const deferred = this.$q.defer()

      if (null !== this.userTeams) {
        deferred.resolve(this.userTeams)
      } else {
        this.$http
          .get(this.UrlGenerator('innova_path_criteria_user_teams'))
          .success((response) => {
            this.userTeams = response
            deferred.resolve(response)
            delete this.userTeamsPromise
          })
          .error((response) => {
            deferred.reject(response)
            delete this.userTeamsPromise
          })

        this.userTeamsPromise = deferred.promise
      }

      return deferred.promise
    }

    return this.userTeamsPromise
  }
}
