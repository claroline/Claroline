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

export default class DocumentModelCreationCtrl {
  constructor($http, $state, CourseService) {
    this.$http = $http
    this.$state = $state
    this.title = Translator.trans('document_model_creation', {}, 'cursus')
    this.documentModel = {
      name: null,
      content: null,
      documentType: null
    }
    this.documentModelErrors = {
      name: null,
      content: null,
      documentType: null
    }
    this.documentTypes = [
      {name: Translator.trans('session_invitation', {}, 'cursus'), value: 0},
      {name: Translator.trans('session_event_invitation', {}, 'cursus'), value: 1},
      {name: Translator.trans('session_certificate', {}, 'cursus'), value: 2},
      {name: Translator.trans('session_event_certificate', {}, 'cursus'), value: 3}
    ]
    this.documentType = null
    this.tinymceOptions = CourseService.getTinymceConfiguration()
  }

  submit() {
    this.resetErrors()

    if (!this.documentModel['name']) {
      this.documentModelErrors['name'] = Translator.trans('form_not_blank_error', {}, 'cursus')
    }

    if (!this.documentModel['content']) {
      this.documentModelErrors['content'] = Translator.trans('form_not_blank_error', {}, 'cursus')
    }

    if (!this.documentType) {
      this.documentModelErrors['documentType'] = Translator.trans('form_not_blank_error', {}, 'cursus')
      this.documentModel['documentType'] = null
    } else {
      this.documentModel['documentType'] = this.documentType['value']
    }

    if (this.isValid()) {
      const url = Routing.generate('api_post_cursus_document_model_creation')
      this.$http.post(url, {documentModelDatas: this.documentModel}).then(() => {
        this.$state.go('document_models_management')
      })
    }
  }

  resetErrors() {
    for (const key in this.documentModelErrors) {
      this.documentModelErrors[key] = null
    }
  }

  isValid() {
    let valid = true

    for (const key in this.documentModelErrors) {
      if (this.documentModelErrors[key]) {
        valid = false
        break
      }
    }

    return valid
  }

  isSessionEvent() {
    return this.documentType && (this.documentType['value'] === 1 || this.documentType['value'] === 3)
  }

  isSession() {
    return this.documentType && (this.documentType['value'] === 0 || this.documentType['value'] === 2)
  }
}
