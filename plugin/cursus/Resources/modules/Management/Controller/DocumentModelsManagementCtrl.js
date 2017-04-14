/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default class DocumentModelsManagementCtrl {
  constructor(NgTableParams, CourseService, DocumentModelService) {
    this.CourseService = CourseService
    this.DocumentModelService = DocumentModelService
    this.models = []
    this.tableParams = new NgTableParams(
      {count: 20},
      {counts: [10, 20, 50, 100], dataset: this.models}
    )
    this.initialize()
    this._addDocumentModelCallback = this._addDocumentModelCallback.bind(this)
    this._updateDocumentModelCallback = this._updateDocumentModelCallback.bind(this)
    this._removeDocumentModelCallback = this._removeDocumentModelCallback.bind(this)
  }

  _addDocumentModelCallback(data) {
    let documentModel = JSON.parse(data)
    documentModel['documentTypeName'] = this.DocumentModelService.getDocumentTypeName(documentModel['documentType'])
    this.models.push(documentModel)
    this.tableParams.reload()
  }

  _updateDocumentModelCallback(data) {
    let documentModel = JSON.parse(data)
    documentModel['documentTypeName'] = this.DocumentModelService.getDocumentTypeName(documentModel['documentType'])
    const index = this.models.findIndex(m => m['id'] === documentModel['id'])

    if (index > -1) {
      this.models[index] = documentModel
      this.tableParams.reload()
    }
  }

  _removeDocumentModelCallback(data) {
    const documentModel = JSON.parse(data)
    const index = this.models.findIndex(m => m['id'] === documentModel['id'])

    if (index > -1) {
      this.models.splice(index, 1)
      this.tableParams.reload()
    }
  }

  initialize() {
    this.DocumentModelService.getAllDocumentModels().then(d => {
      d.forEach(m => {
        m['documentTypeName'] = this.DocumentModelService.getDocumentTypeName(m['documentType'])
        this.models.push(m)
      })
    })
  }

  deleteDocumentModel(modelId) {
    this.DocumentModelService.deleteDocumentModel(modelId, this._removeDocumentModelCallback)
  }
}