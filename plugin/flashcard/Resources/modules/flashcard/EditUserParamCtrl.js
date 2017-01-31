/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view
 * the LICENSE
 * file that was distributed with this source code.
 */

import NotBlank from '#/main/core/form/Validator/NotBlank'

export default class EditUserParamCtrl {
  constructor(service, $routeParams, $location) {
    this.deck = service.getDeck()
    this.deckNode = service.getDeckNode()
    this.canEdit = service._canEdit
    this.nexturl = $routeParams.nexturl
    this.userPref = {}
    this.themeField = [
      'type',
      'select',
      {
        values: [],
        label: 'theme',
        choice_name: 'name',
        choice_value: 'value',
        validators: [new NotBlank()]
      }
    ]

    this.errorMessage = null
    this.errors = []
    this._service = service
    this.$location = $location

    service.getUserPreference(this.deck).then(d => this.userPref = d.data)
    service.getAllThemes().then(d => this.themeField[2].values = d.data)
  }

  editUserParam(form) {
    if (form.$valid) {
      this._service.editUserParam(
          this.deck,
          this.userPref.new_card_day,
          this.userPref.theme
        ).then(
        d => {
          this.deck = d.data
          this.$location.search('nexturl', null)
          this.$location.path(this.nexturl)
        },
        d => {
          this.errorMessage = 'errors.deck.edition_failure'
          this.errors = d.data
        }
      )
    }
  }
}
