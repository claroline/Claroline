/*global Routing*/

export default class ResultsService {
  constructor ($http, uploader) {
    this.$http = $http
    this.uploader = uploader
    this._resultId = ResultsService._getGlobal('resultId')
    this._resultMax = ResultsService._getGlobal('resultMax')
    this._marks = ResultsService._getGlobal('resultMarks')
    this._users = ResultsService._getGlobal('workspaceUsers')
    this._isReadOnly = ResultsService._getGlobal('isReadOnly')
  }

  getResults () {
    return this._marks
  }

  getUsers () {
    return this._users
  }

  getMaximumMark() {
    return this._resultMax
  }

  //we'll also support ',' as a decimal point because it's what we mainly use as french speakers.
  formatMark(mark) {
    return parseInt(mark.replace('.', ','))
  }

  isReadOnly() {
    return this._isReadOnly
  }

  createMark (props, onFail) {
    const user = this._users.find(user => user.name === props.user)
    const result = { name: props.user, mark: props.mark }
    const url = Routing.generate('claro_create_mark', {
      id: this._resultId,
      userId: user.id
    })

    this._marks.push(result)

    this.$http
      .post(url, { mark: props.mark })
      .then(
        response => { result.markId = response.data },
        () => {
          this._deleteMark(result)
          onFail()
        }
      )
  }

  deleteMark (mark, onFail) {
    const url = Routing.generate('claro_delete_mark', {
      id: mark.markId
    })

    this._deleteMark(mark)

    this.$http
      .delete(url)
      .then(null, () => {
        this._marks.push(mark)
        onFail()
      })
  }

  editMark (originalMark, newValue, onFail) {
    if (originalMark.mark === newValue) {
      return
    }

    const originalValue = originalMark.mark
    const url = Routing.generate('claro_edit_mark', {
      id: originalMark.markId
    })

    originalMark.mark = newValue

    this.$http
      .put(url, { value: newValue })
      .then(null, () => {
        originalMark.mark = originalValue
        onFail()
      })
  }

  importMarks (file, type, onFail) {
    const url = Routing.generate('claro_import_marks', {
      id: this._resultId,
      type: type
    })
    this.uploader
      .upload({ url, data: { file } })
      .then(
        response => this._marks.push(...response.data),
        response => onFail(response.data),
        event => {
          const progress = parseInt(100.0 * event.loaded / event.total)
        }
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

  _deleteMark (mark) {
    this._marks.splice(this._marks.indexOf(mark), 1)
  }
}
