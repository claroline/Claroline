/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default class KeywordsManagementCtrl {
  constructor(NgTableParams, ClacoFormService, KeywordService) {
    this.ClacoFormService = ClacoFormService
    this.KeywordService = KeywordService
    this.keywords = KeywordService.getKeywords()
    this.sortedKeywords = {}
    this.tableParams = new NgTableParams(
      {count: 20},
      {counts: [10, 20, 50, 100], dataset: this.keywords}
    )
    this._addKeywordCallback = this._addKeywordCallback.bind(this)
    this._updateKeywordCallback = this._updateKeywordCallback.bind(this)
    this._removeKeywordCallback = this._removeKeywordCallback.bind(this)
    this.initialize()
  }

  _addKeywordCallback(data) {
    this.KeywordService._addKeywordCallback(data)
    this.tableParams.reload()
  }

  _updateKeywordCallback(data) {
    this.KeywordService._updateKeywordCallback(data)
    this.tableParams.reload()
  }

  _removeKeywordCallback(data) {
    this.KeywordService._removeKeywordCallback(data)
    this.tableParams.reload()
  }

  initialize() {
    this.ClacoFormService.clearMessages()
  }

  canEdit() {
    return this.ClacoFormService.getCanEdit()
  }

  createKeyword() {
    this.KeywordService.createKeyword(this.ClacoFormService.getResourceId(), this._addKeywordCallback)
  }

  editKeyword(keyword) {
    this.KeywordService.editKeyword(keyword, this.ClacoFormService.getResourceId(), this._updateKeywordCallback)
  }

  deleteKeyword(keyword) {
    this.KeywordService.deleteKeyword(keyword, this._removeKeywordCallback)
  }
}