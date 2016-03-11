export default class ResultsService {
  constructor ($http) {
    this.$http = $http
    this._resultId = ResultsService._getGlobal('resultId')
    this._marks = ResultsService._getGlobal('resultMarks')
    this._users = ResultsService._getGlobal('workspaceUsers')
  }

  getResults () {
    return this._marks
  }

  getUsers () {
    return this._users
  }

  createMark (userId, mark) {
    const url = Routing.generate('claro_create_mark', {
      id: this._resultId,
      userId
    })

    return this.$http.post(url, { mark })
  }

  deleteMark (id) {
    return this.$http.delete(
      Routing.generate('claro_delete_mark', { id })
    )
  }

  static _getGlobal (name) {
    if (typeof window[name] === 'undefined') {
      throw new Error(
        `Expected ${name} to be exposed in a window.${name} variable`
      )
    }

    return window[name]
  }
}
