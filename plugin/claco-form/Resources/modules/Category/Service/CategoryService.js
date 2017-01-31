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
import categoryFormTemplate from '../Partial/category_form_modal.html'

export default class CategoryService {
  constructor($http, $uibModal, ClarolineAPIService) {
    this.$http = $http
    this.$uibModal = $uibModal
    this.ClarolineAPIService = ClarolineAPIService
    this.canEdit = CategoryService._getGlobal('canEdit')
    this.isCategoryManager = CategoryService._getGlobal('isCategoryManager')
    this.workspaceId = CategoryService._getGlobal('workspaceId')
    this.categories = CategoryService._getGlobal('categories')
    this._addCategoryCallback = this._addCategoryCallback.bind(this)
    this._updateCategoryCallback = this._updateCategoryCallback.bind(this)
    this._removeCategoryCallback = this._removeCategoryCallback.bind(this)
  }

  _addCategoryCallback(data) {
    let category = JSON.parse(data)
    this.formatCategory(category)
    this.categories.push(category)
  }

  _updateCategoryCallback(data) {
    let category = JSON.parse(data)
    this.formatCategory(category)
    const index = this.categories.findIndex(c => c['id'] === category['id'])

    if (index > -1) {
      this.categories[index] = category
    }
  }

  _removeCategoryCallback(data) {
    const category = JSON.parse(data)
    const index = this.categories.findIndex(c => c['id'] === category['id'])

    if (index > -1) {
      this.categories.splice(index, 1)
    }
  }

  getIsCategoryManager() {
    return this.isCategoryManager
  }

  getWorkspaceId() {
    return this.workspaceId
  }

  getCategories() {
    return this.categories
  }

  formatCategory(category) {
    let managersName = ''
    let index = 0
    const length = category['managers'].length - 1
    category['managers'].forEach(m => {
      managersName += `${m['firstName']} ${m['lastName']}`

      if (index < length) {
        managersName += ', '
      }
      ++index
    })
    category['managersName'] = managersName
  }

  createCategory(resourceId, workspaceId, callback = null) {
    const addCallback = callback !== null ? callback : this._addCategoryCallback
    this.$uibModal.open({
      template: categoryFormTemplate,
      controller: 'CategoryCreationModalCtrl',
      controllerAs: 'cfc',
      resolve: {
        resourceId: () => { return resourceId },
        workspaceId: () => { return workspaceId },
        title: () => { return Translator.trans('create_a_category', {}, 'clacoform') },
        callback: () => { return addCallback }
      }
    })
  }

  editCategory(category, workspaceId, callback = null) {
    const updateCallback = callback !== null ? callback : this._updateCategoryCallback
    this.$uibModal.open({
      template: categoryFormTemplate,
      controller: 'CategoryEditionModalCtrl',
      controllerAs: 'cfc',
      resolve: {
        workspaceId: () => { return workspaceId },
        category: () => { return category },
        title: () => { return Translator.trans('edit_category', {}, 'clacoform') },
        callback: () => { return updateCallback }
      }
    })
  }

  deleteCategory(category, callback = null) {
    const url = Routing.generate('claro_claco_form_category_delete', {category: category['id']})
    const deleteCallback = callback !== null ? callback : this._removeCategoryCallback

    this.ClarolineAPIService.confirm(
      {url, method: 'DELETE'},
      deleteCallback,
      Translator.trans('delete_category', {}, 'clacoform'),
      Translator.trans('delete_category_confirm_message', {name: category['name']}, 'clacoform')
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