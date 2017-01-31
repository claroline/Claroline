/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*global Translator*/

export default class FieldsManagementCtrl {
  constructor(NgTableParams, ClacoFormService, FieldService) {
    this.ClacoFormService = ClacoFormService
    this.FieldService = FieldService
    this.fields = FieldService.getFields()
    this.tableParams = new NgTableParams(
      {count: 20},
      {counts: [10, 20, 50, 100], dataset: this.fields}
    )
    this.types = {
      1: Translator.trans('text', {}, 'platform'),
      2: Translator.trans('number', {}, 'platform'),
      3: Translator.trans('date', {}, 'platform'),
      4: Translator.trans('radio', {}, 'platform'),
      5: Translator.trans('select', {}, 'platform'),
      6: Translator.trans('checkboxes', {}, 'platform'),
      7: Translator.trans('country', {}, 'platform'),
      8: Translator.trans('email', {}, 'platform')
    }
    this._addFieldCallback = this._addFieldCallback.bind(this)
    this._updateFieldCallback = this._updateFieldCallback.bind(this)
    this._removeFieldCallback = this._removeFieldCallback.bind(this)
    this.initialize()
  }

  _addFieldCallback(data) {
    this.FieldService._addFieldCallback(data)
    this.tableParams.reload()
  }

  _updateFieldCallback(data) {
    this.FieldService._updateFieldCallback(data)
    this.tableParams.reload()
  }

  _removeFieldCallback(data) {
    this.FieldService._removeFieldCallback(data)
    this.tableParams.reload()
  }

  initialize() {
    this.ClacoFormService.clearMessages()
  }

  canEdit() {
    return this.ClacoFormService.getCanEdit()
  }

  createField() {
    this.FieldService.createField(this.ClacoFormService.getResourceId(), this._addFieldCallback)
  }

  editField(field) {
    this.FieldService.editField(field, this.ClacoFormService.getResourceId(), this._updateFieldCallback)
  }

  deleteField(field) {
    this.FieldService.deleteField(field, this._removeFieldCallback)
  }
}