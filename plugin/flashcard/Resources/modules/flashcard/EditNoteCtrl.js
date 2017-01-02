/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view
 * the LICENSE
 * file that was distributed with this source code.
 */

export default class EditNoteCtrl {
  constructor(service, $routeParams, $location) {
    this.deck = service.getDeck()
    this.deckNode = service.getDeckNode()
    this.canEdit = service._canEdit
    this.note = null
    this.nexturl = $routeParams.nexturl

    this.errorMessage = null
    this.errors = []
    this._service = service
    this.$location = $location

    service.findNote($routeParams.id).then(d => this.note = d.data)
  }

  editNote(form) {
    if (form.$valid) {
      this._service.editNote(this.note, this.note.field_values).then(
        d => {
          this.note = d.data
          this.$location.search('nexturl', null)
          this.$location.path(this.nexturl)
        },
        d => {
          this.errorMessage = 'errors.note.edition_failure'
          this.errors = d.data
        }
      )
    }
  }
}
