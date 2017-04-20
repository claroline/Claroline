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
import documentSelectionTemplate from '../Partial/document_model_selection_modal.html'

export default class DocumentModelService {
  constructor($http, $uibModal, ClarolineAPIService) {
    this.$http = $http
    this.$uibModal = $uibModal
    this.ClarolineAPIService = ClarolineAPIService
  }

  getAllDocumentModels() {
    const url = Routing.generate('api_get_cursus_document_models')

    return this.$http.get(url).then(d => {
      if (d['status'] === 200) {
        return JSON.parse(d['data'])
      }
    })
  }

  getDocumentModelById(modelId) {
    const url = Routing.generate('api_get_cursus_document_model', {documentModel: modelId})

    return this.$http.get(url).then(d => {
      if (d['status'] === 200) {
        return JSON.parse(d['data'])
      }
    })
  }

  getDocumentModelsByType(type) {
    const url = Routing.generate('api_get_cursus_document_models_by_type', {type: type})

    return this.$http.get(url).then(d => {
      if (d['status'] === 200) {
        return JSON.parse(d['data'])
      }
    })
  }

  getPopulatedDocumentModelsByType(type, sourceId) {
    const url = Routing.generate('api_get_cursus_populated_document_models_by_type', {type: type, sourceId: sourceId})

    return this.$http.get(url).then(d => {
      if (d['status'] === 200) {
        return d['data']
      }
    })
  }

  getDocumentTypeName(documentType) {
    let name = ''

    switch (documentType) {
      case 0 :
        name = Translator.trans('session_invitation', {}, 'cursus')
        break
      case 1 :
        name = Translator.trans('session_event_invitation', {}, 'cursus')
        break
      case 2 :
        name = Translator.trans('session_certificate', {}, 'cursus')
        break
      case 3 :
        name = Translator.trans('session_event_certificate', {}, 'cursus')
        break
      default :
        break
    }

    return name
  }

  deleteDocumentModel(documentModelId, callback = null) {
    const url = Routing.generate('api_delete_cursus_document_model', {documentModel: documentModelId})
    this.ClarolineAPIService.confirm(
      {url, method: 'DELETE'},
      callback,
      Translator.trans('delete_document_model', {}, 'cursus'),
      Translator.trans('delete_document_model_confirm_message', {}, 'cursus')
    )
  }

  displayDocumentSelection(datas, documentType, callback = null) {
    this.$uibModal.open({
      template: documentSelectionTemplate,
      controller: 'DocumentModelSelectionModalCtrl',
      controllerAs: 'cmc',
      resolve: {
        datas: () => { return datas },
        documentType: () => { return documentType },
        callback: () => { return callback }
      }
    })
  }
}