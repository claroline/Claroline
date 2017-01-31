/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*global Routing*/
/*global Translator*/

export default class CommentService {
  constructor($http, ClarolineAPIService) {
    this.$http = $http
    this.ClarolineAPIService = ClarolineAPIService
    this.canEdit = CommentService._getGlobal('canEdit')
    this.comments = []
    this._addCommentCallback = this._addCommentCallback.bind(this)
  }

  _addCommentCallback(data) {
    const comment = JSON.parse(data)
    this.comments.unshift(comment)
  }

  _updateCommentCallback(data) {
    const comment = JSON.parse(data)
    const index = this.comments.findIndex(c => c['id'] === comment['id'])

    if (index > -1) {
      this.comments[index] = comment
    }
  }

  _removeCommentCallback(data) {
    const comment = JSON.parse(data)
    const index = this.comments.findIndex(c => c['id'] === comment['id'])

    if (index > -1) {
      this.comments.splice(index, 1)
    }
  }

  initializeComments(entryId) {
    const url = Routing.generate('claro_claco_form_entry_comments_retrieve', {entry: entryId})
    this.$http.get(url).then(d => {
      if (d['status'] === 200) {
        const retrievedComments = JSON.parse(d['data'])
        this.comments.splice(0, this.comments.length)
        retrievedComments.forEach(c => this.comments.push(c))
      }
    })
  }

  getComments() {
    return this.comments
  }

  createComment(entryId, content, callback = null) {
    const url = Routing.generate('claro_claco_form_entry_comment_create', {entry: entryId})
    const addCallback = callback !== null ? callback : this._addCommentCallback
    this.$http.post(url, {commentData: content}).then(d => {
      if (d['status'] === 200) {
        addCallback(d['data'])
      }
    })
  }

  editComment(comment, callback = null) {
    const url = Routing.generate('claro_claco_form_entry_comment_edit', {comment: comment['id']})
    const updateCallback = callback !== null ? callback : this._updateCommentCallback
    this.$http.put(url, {commentData: comment['content']}).then(d => {
      if (d['status'] === 200) {
        updateCallback(d['data'])
      }
    })
  }

  deleteComment(comment, callback = null) {
    const url = Routing.generate('claro_claco_form_entry_comment_delete', {comment: comment['id']})
    const deleteCallback = callback !== null ? callback : this._removeCommentCallback

    this.ClarolineAPIService.confirm(
      {url, method: 'DELETE'},
      deleteCallback,
      Translator.trans('delete_comment', {}, 'clacoform'),
      Translator.trans('delete_comment_confirm_message', {}, 'clacoform')
    )
  }

  activateComment(comment, callback = null) {
    const url = Routing.generate('claro_claco_form_entry_comment_activate', {comment: comment['id']})
    const updateCallback = callback !== null ? callback : this._updateCommentCallback
    this.$http.put(url).then(d => {
      if (d['status'] === 200) {
        updateCallback(d['data'])
      }
    })
  }

  blockComment(comment, callback = null) {
    const url = Routing.generate('claro_claco_form_entry_comment_block', {comment: comment['id']})
    const updateCallback = callback !== null ? callback : this._updateCommentCallback
    this.$http.put(url).then(d => {
      if (d['status'] === 200) {
        updateCallback(d['data'])
      }
    })
  }

  static _getGlobal(name) {
    if (typeof window[name] === 'undefined') {
      throw new Error(
        `Expected ${name} to be exposed in a window.${name} variable`
      )
    }

    return window[name]
  }
}