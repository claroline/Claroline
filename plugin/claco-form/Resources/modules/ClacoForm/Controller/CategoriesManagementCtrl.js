/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default class CategoriesManagementCtrl {
  constructor(NgTableParams, ClacoFormService, CategoryService) {
    this.ClacoFormService = ClacoFormService
    this.CategoryService = CategoryService
    this.categories = CategoryService.getCategories()
    this.tableParams = new NgTableParams(
      {count: 20},
      {counts: [10, 20, 50, 100], dataset: this.categories}
    )
    this._addCategoryCallback = this._addCategoryCallback.bind(this)
    this._updateCategoryCallback = this._updateCategoryCallback.bind(this)
    this._removeCategoryCallback = this._removeCategoryCallback.bind(this)
    this.initialize()
  }

  _addCategoryCallback(data) {
    this.CategoryService._addCategoryCallback(data)
    this.tableParams.reload()
  }

  _updateCategoryCallback(data) {
    this.CategoryService._updateCategoryCallback(data)
    this.tableParams.reload()
  }

  _removeCategoryCallback(data) {
    this.CategoryService._removeCategoryCallback(data)
    this.tableParams.reload()
  }

  initialize() {
    this.ClacoFormService.clearMessages()
    this.categories.forEach(c => this.CategoryService.formatCategory(c))
  }

  canEdit() {
    return this.ClacoFormService.getCanEdit()
  }

  createCategory() {
    this.CategoryService.createCategory(
      this.ClacoFormService.getResourceId(),
      this.CategoryService.getWorkspaceId(),
      this._addCategoryCallback
    )
  }

  editCategory(category) {
    this.CategoryService.editCategory(category, this.CategoryService.getWorkspaceId(), this._updateCategoryCallback)
  }

  deleteCategory(category) {
    this.CategoryService.deleteCategory(category, this._removeCategoryCallback)
  }
}