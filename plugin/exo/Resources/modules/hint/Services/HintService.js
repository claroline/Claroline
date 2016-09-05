
export default class HintService {
  /**
   * Constructor.
   *
   * @param {object} $q
   * @param {object} $http
   * @param {function} url
   * @param {UserPaperService} UserPaperService
   */
  constructor($q, $http, url, UserPaperService) {
    this.$q = $q
    this.$http = $http
    this.UrlGenerator = url
    this.UserPaperService = UserPaperService
  }

  /**
   * Get hint data from Paper if it has already been used.
   *
   * @param {array} paperHints
   * @param {object} hint
   *
   * @returns {object|null}
   */
  getHintFromPaper(paperHints, hint) {
    let used = null
    if (0 !== paperHints.length) {
      for (let i = 0; i < paperHints.length; i++) {
        if (paperHints[i].id === hint.id) {
          used = paperHints[i]

          break // Stop searching
        }
      }
    }

    return used
  }

  /**
   * Checks whether a hint has been used or not.
   *
   * @param {array} paperHints
   * @param {object} hint
   * @returns {boolean}
   */
  isHintUsed(paperHints, hint) {
    return null !== this.getHintFromPaper(paperHints, hint)
  }

  /**
   * Use an hint.
   *
   * @param {array} paperHints
   * @param {object} hint
   *
   * @returns {Promise}
   */
  useHint(paperHints, hint) {
    const deferred = this.$q.defer()


    if (!hint.value) {
      // Hint data re not loaded => call the server
      const paper = this.UserPaperService.getPaper()

      this.$http
        .get(
          this.UrlGenerator('exercise_hint_show', {paperId: paper.id, id: hint.id})
        )
        .success(response => {
          // Update question Paper with used hint
          paperHints.push({
            id: hint.id,
            penalty: hint.penalty,
            value: response
          })

          // Update Hint for later use
          hint.value = response

          deferred.resolve(hint)
        })
        .error(() => {
          deferred.reject(null)
        })
    } else {
      paperHints.push({
        id: hint.id,
        penalty: hint.penalty,
        value: hint.value
      })
      
      deferred.resolve(hint)
    }

    return deferred.promise
  }
}
