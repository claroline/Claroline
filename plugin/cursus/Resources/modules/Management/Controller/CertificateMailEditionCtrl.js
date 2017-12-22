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

export default class CertificateMailEditionCtrl {
  constructor($http, $state, CourseService, DocumentModelService) {
    this.$http = $http
    this.$state = $state
    this.DocumentModelService = DocumentModelService
    this.title = Translator.trans('certificate_mail_edition', {}, 'cursus')
    this.source = {}
    this.documentModel = {
      id: null,
      name: null,
      content: null,
      documentType: 4
    }
    this.tinymceOptions = CourseService.getTinymceConfiguration()
    this.initializeDocumentModel()
  }

  initializeDocumentModel() {
    this.DocumentModelService.getCertificateMailDocumentModel().then(d => {
      this.documentModel['id'] = d['id']
      this.documentModel['name'] = d['name']
      this.documentModel['content'] = d['content']
    })
  }

  submit() {
    const url = Routing.generate('api_put_cursus_document_model_edition', {documentModel: this.documentModel['id']})
    this.$http.put(url, {documentModelDatas: this.documentModel}).then(() => {
      this.$state.go('configuration')
    })
  }
}
