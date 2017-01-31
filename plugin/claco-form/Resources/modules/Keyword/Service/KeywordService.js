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
import keywordFormTemplate from '../Partial/keyword_form_modal.html'

export default class KeywordService {
  constructor($http, $uibModal, ClarolineAPIService) {
    this.$http = $http
    this.$uibModal = $uibModal
    this.ClarolineAPIService = ClarolineAPIService
    this.canEdit = KeywordService._getGlobal('canEdit')
    this.keywords = KeywordService._getGlobal('keywords')
    this._addKeywordCallback = this._addKeywordCallback.bind(this)
    this._updateKeywordCallback = this._updateKeywordCallback.bind(this)
    this._removeKeywordCallback = this._removeKeywordCallback.bind(this)
  }

  _addKeywordCallback(data) {
    const keyword = JSON.parse(data)
    this.keywords.push(keyword)
  }

  _updateKeywordCallback(data) {
    const keyword = JSON.parse(data)
    const index = this.keywords.findIndex(k => k['id'] === keyword['id'])

    if (index > -1) {
      this.keywords[index] = keyword
    }
  }

  _removeKeywordCallback(data) {
    const keyword = JSON.parse(data)
    const index = this.keywords.findIndex(k => k['id'] === keyword['id'])

    if (index > -1) {
      this.keywords.splice(index, 1)
    }
  }

  getKeywords() {
    return this.keywords
  }

  createKeyword(resourceId, callback = null) {
    const addCallback = callback !== null ? callback : this._addKeywordCallback
    this.$uibModal.open({
      template: keywordFormTemplate,
      controller: 'KeywordCreationModalCtrl',
      controllerAs: 'cfc',
      resolve: {
        resourceId: () => { return resourceId },
        title: () => { return Translator.trans('create_a_keyword', {}, 'clacoform') },
        callback: () => { return addCallback }
      }
    })
  }

  editKeyword(keyword, resourceId, callback = null) {
    const updateCallback = callback !== null ? callback : this._updateKeywordCallback
    this.$uibModal.open({
      template: keywordFormTemplate,
      controller: 'KeywordEditionModalCtrl',
      controllerAs: 'cfc',
      resolve: {
        resourceId: () => { return resourceId },
        keyword: () => { return keyword },
        title: () => { return Translator.trans('edit_keyword', {}, 'clacoform') },
        callback: () => { return updateCallback }
      }
    })
  }

  deleteKeyword(keyword, callback = null) {
    const url = Routing.generate('claro_claco_form_keyword_delete', {keyword: keyword['id']})
    const deleteCallback = callback !== null ? callback : this._removeKeywordCallback

    this.ClarolineAPIService.confirm(
      {url, method: 'DELETE'},
      deleteCallback,
      Translator.trans('delete_keyword', {}, 'clacoform'),
      Translator.trans('delete_keyword_confirm_message', {name: keyword['name']}, 'clacoform')
    )
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