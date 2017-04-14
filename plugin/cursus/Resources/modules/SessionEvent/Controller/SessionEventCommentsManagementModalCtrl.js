/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default class SessionEventCommentsManagementModalCtrl {
  constructor($sce, NgTableParams, CourseService, SessionEventService, sessionEvent) {
    this.$sce = $sce
    this.SessionEventService = SessionEventService
    this.sessionEvent = sessionEvent
    this.isCollapsed = false
    this.comments = this.sessionEvent['comments']
    this.tableParams = new NgTableParams(
      {count: 20},
      {counts: [10, 20, 50, 100], dataset: this.comments}
    )
    this.tinymceOptions = CourseService.getTinymceConfiguration()
    this.isCreationFormVisible = false
    this.updateId = null
    this.newComment = null
    this.updateComment = null
  }

  addCommentCallback(comment) {
    this.comments.push(comment)
    this.tableParams.reload()
  }

  updateCommentCallback(comment) {
    const index = this.comments.findIndex(c => c['id'] === comment['id'])

    if (index > -1) {
      this.comments[index]['content'] = comment['content']
      this.comments[index]['editionDate'] = comment['editionDate']
      this.tableParams.reload()
    }
  }

  removeCommentCallback(commentId) {
    const index = this.comments.findIndex(c => c['id'] === commentId)

    if (index > -1) {
      this.comments.splice(index, 1)
      this.tableParams.reload()
    }
  }

  displayCommentCreationForm() {
    this.isCreationFormVisible = true
  }

  confirmCommentCreation() {
    if (this.newComment) {
      this.SessionEventService.createComment(this.sessionEvent['id'], this.newComment).then(d => this.addCommentCallback(d))
    }
    this.isCreationFormVisible = false
    this.newComment = null
  }

  cancelCommentCreation() {
    this.isCreationFormVisible = false
    this.newComment = null
  }

  displayCommentEditionForm(comment) {
    this.updateId = comment['id']
    this.updateComment = comment['content']
  }

  confirmCommentEdition() {
    if (this.updateId, this.updateComment) {
      this.SessionEventService.editComment(this.updateId, this.updateComment).then(d => this.updateCommentCallback(d))
    }
    this.updateId = null
    this.updateComment = null
  }

  cancelCommentEdition() {
    this.updateId = null
    this.updateComment = null
  }

  deleteComment(commentId) {
    this.SessionEventService.deleteComment(commentId).then(d => {
      if (d === 'success') {
        this.removeCommentCallback(commentId)
      }
    })
  }
}
