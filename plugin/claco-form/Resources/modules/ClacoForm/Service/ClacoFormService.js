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
import $ from 'jquery'

export default class ClacoFormService {
  constructor($http, $uibModal, EntryService) {
    this.$http = $http
    this.$uibModal = $uibModal
    this.EntryService = EntryService
    this.isAnon = ClacoFormService._getGlobal('isAnon')
    this.canEdit = ClacoFormService._getGlobal('canEdit')
    this.userId = ClacoFormService._getGlobal('userId')
    this.resourceId = ClacoFormService._getGlobal('resourceId')
    this.template = ClacoFormService._getGlobal('template')
    this.resourceDetails = ClacoFormService._getGlobal('resourceDetails')
    this.resourceNodeId = ClacoFormService._getGlobal('resourceNodeId')
    this.resourceNodeName = ClacoFormService._getGlobal('resourceNodeName')
    this.successMessage = null
    this.errorMessage = null
  }

  getIsAnon() {
    return this.isAnon
  }

  getUserId() {
    return this.userId
  }

  getCanEdit() {
    return this.canEdit
  }

  getCanCreateEntry() {
    return this.resourceDetails['creation_enabled'] &&
      !(this.isAnon && this.resourceDetails['max_entries'] > 0) &&
      !(this.resourceDetails['max_entries'] > 0 && this.EntryService.getNbMyEntries() >= this.resourceDetails['max_entries'])
  }

  getCanSearchEntry() {
    return this.resourceDetails['search_enabled'] || this.canEdit || !this.isAnon
  }

  getResourceId() {
    return this.resourceId
  }

  getTemplate() {
    return this.template
  }

  getResourceDetails() {
    let details = {}

    for (const key in this.resourceDetails) {
      details[key] = this.resourceDetails[key]
    }

    return details
  }

  getResourceNodeId() {
    return this.resourceNodeId
  }

  getResourceNodeName() {
    return this.resourceNodeName
  }

  getSuccessMessage() {
    return this.successMessage
  }

  setSuccessMessage(message) {
    this.successMessage = message
  }

  clearSuccessMessage() {
    this.successMessage = null
  }

  getErrorMessage() {
    return this.errorMessage
  }

  setErrorMessage(message) {
    this.errorMessage = message
  }

  clearErrorMessage() {
    this.errorMessage = null
  }

  clearMessages() {
    this.clearSuccessMessage()
    this.clearErrorMessage()
  }

  saveConfiguration(resourceId, config) {
    const url = Routing.generate('claro_claco_form_configuration_edit', {clacoForm: resourceId})

    return this.$http.put(url, {configData: config}).then(d => {
      if (d['status'] === 200) {
        this.successMessage = Translator.trans('config_success_message', {}, 'clacoform')
        this.resourceDetails = d['data']

        return true
      }
    })
  }

  saveTemplate(resourceId, template) {
    const url = Routing.generate('claro_claco_form_template_edit', {clacoForm: resourceId})

    return this.$http.put(url, {template: template}).then(d => {
      if (d['status'] === 200) {
        this.successMessage = Translator.trans('template_success_message', {}, 'clacoform')
        this.template = d['data']['template']

        return true
      }
    })
  }

  getRandomEntryId(resourceId) {
    const url = Routing.generate('claro_claco_form_entry_random', {clacoForm: resourceId})

    return this.$http.get(url).then(d => {
      if (d['status'] === 200) {
        return d['data']
      }
    })
  }

  getTinymceConfiguration() {
    let tinymce = window.tinymce
    tinymce.claroline.init = tinymce.claroline.init || {}
    tinymce.claroline.plugins = tinymce.claroline.plugins || {}

    let plugins = [
      'autoresize advlist autolink lists link image charmap print preview hr anchor pagebreak',
      'searchreplace wordcount visualblocks visualchars fullscreen',
      'insertdatetime media nonbreaking save table directionality',
      'template paste textcolor emoticons code -accordion -mention -codemirror'
    ]
    let toolbar = 'bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | fullscreen displayAllButtons'

    $.each(tinymce.claroline.plugins, (key, value) => {
      if ('autosave' != key &&  value === true) {
        plugins.push(key)
        toolbar += ' ' + key
      }
    })

    let config = {}

    for (const prop in tinymce.claroline.configuration) {
      if (tinymce.claroline.configuration.hasOwnProperty(prop)) {
        config[prop] = tinymce.claroline.configuration[prop]
      }
    }
    config.plugins = plugins
    config.toolbar1 = toolbar
    config.trusted = true
    config.format = 'html'

    return config
  }

  removeAccent(str) {
    let convertedStr = str
    convertedStr = convertedStr.replace('Ç', 'C')
    convertedStr = convertedStr.replace('ç', 'c')
    convertedStr = convertedStr.replace('è', 'e')
    convertedStr = convertedStr.replace('é', 'e')
    convertedStr = convertedStr.replace('ê', 'e')
    convertedStr = convertedStr.replace('ë', 'e')
    convertedStr = convertedStr.replace('È', 'E')
    convertedStr = convertedStr.replace('É', 'E')
    convertedStr = convertedStr.replace('Ê', 'E')
    convertedStr = convertedStr.replace('Ë', 'E')
    convertedStr = convertedStr.replace('à', 'a')
    convertedStr = convertedStr.replace('á', 'a')
    convertedStr = convertedStr.replace('â', 'a')
    convertedStr = convertedStr.replace('ã', 'a')
    convertedStr = convertedStr.replace('ä', 'a')
    convertedStr = convertedStr.replace('ä', 'a')
    convertedStr = convertedStr.replace('@', 'A')
    convertedStr = convertedStr.replace('À', 'A')
    convertedStr = convertedStr.replace('Á', 'A')
    convertedStr = convertedStr.replace('Â', 'A')
    convertedStr = convertedStr.replace('Ã', 'A')
    convertedStr = convertedStr.replace('Ä', 'A')
    convertedStr = convertedStr.replace('Å', 'A')
    convertedStr = convertedStr.replace('ì', 'i')
    convertedStr = convertedStr.replace('í', 'i')
    convertedStr = convertedStr.replace('î', 'i')
    convertedStr = convertedStr.replace('ï', 'i')
    convertedStr = convertedStr.replace('Ì', 'I')
    convertedStr = convertedStr.replace('Í', 'I')
    convertedStr = convertedStr.replace('Î', 'I')
    convertedStr = convertedStr.replace('Ï', 'I')
    convertedStr = convertedStr.replace('ð', 'o')
    convertedStr = convertedStr.replace('ò', 'o')
    convertedStr = convertedStr.replace('ó', 'o')
    convertedStr = convertedStr.replace('ô', 'o')
    convertedStr = convertedStr.replace('õ', 'o')
    convertedStr = convertedStr.replace('ö', 'o')
    convertedStr = convertedStr.replace('Ò', 'O')
    convertedStr = convertedStr.replace('Ó', 'O')
    convertedStr = convertedStr.replace('Ô', 'O')
    convertedStr = convertedStr.replace('Õ', 'O')
    convertedStr = convertedStr.replace('Ö', 'O')
    convertedStr = convertedStr.replace('ù', 'u')
    convertedStr = convertedStr.replace('ú', 'u')
    convertedStr = convertedStr.replace('û', 'u')
    convertedStr = convertedStr.replace('ü', 'u')
    convertedStr = convertedStr.replace('Ù', 'U')
    convertedStr = convertedStr.replace('Ú', 'U')
    convertedStr = convertedStr.replace('Û', 'U')
    convertedStr = convertedStr.replace('Ü', 'U')
    convertedStr = convertedStr.replace('ý', 'y')
    convertedStr = convertedStr.replace('ÿ', 'y')
    convertedStr = convertedStr.replace('Ý', 'Y')

    return convertedStr
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