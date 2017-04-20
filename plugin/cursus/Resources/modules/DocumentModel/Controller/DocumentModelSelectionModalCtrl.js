/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*global Routing*/

export default class DocumentModelSelectionModalCtrl {
  constructor($http, $uibModalInstance, DocumentModelService, datas, documentType, callback) {
    this.$http = $http
    this.$uibModalInstance = $uibModalInstance
    this.DocumentModelService = DocumentModelService
    this.datas = datas
    this.documentType = documentType
    this.title = DocumentModelService.getDocumentTypeName(documentType)
    this.callback = callback
    this.documentModel = {}
    this.documentModels = []
    this.initialize()
  }

  initialize() {
    this.DocumentModelService.getPopulatedDocumentModelsByType(this.documentType, this.datas['id']).then(d => {
      d.forEach(dc => this.documentModels.push(dc))
    })
  }

  submit() {
    if (this.documentModel) {
      const url = Routing.generate('api_post_cursus_document_send', {documentModel: this.documentModel['id'], sourceId: this.datas['id']})
      this.$http.post(url).then(d => {
        if (d['status'] === 200) {
          if (this.callback) {
            this.callback(d['data'])
          }
          this.$uibModalInstance.close()
        }
      })
    }
  }
}
