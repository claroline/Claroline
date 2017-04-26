/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

export default class TemplateManagementCtrl {
  constructor($state, ClacoFormService, FieldService) {
    this.$state = $state
    this.ClacoFormService = ClacoFormService
    this.FieldService = FieldService
    this.resourceId = ClacoFormService.getResourceId()
    this.template = ClacoFormService.getTemplate()
    this.fields = FieldService.getFields()
    this.mandatory = []
    this.optional = []
    this.requiredErrors = []
    this.duplicatedErrors = []
    this.tinymceOptions = ClacoFormService.getTinymceConfiguration()
    this.initialize()
  }

  initialize() {
    this.ClacoFormService.clearMessages()
    this.fields.forEach(f => {
      if (!f['hidden']) {
        if (f['required']) {
          this.mandatory.push(f)
        } else {
          this.optional.push(f)
        }
      }
    })
  }

  canEdit() {
    return this.ClacoFormService.getCanEdit()
  }

  submit() {
    if (this.isValid()) {
      this.ClacoFormService.saveTemplate(this.resourceId, this.template).then(d => {
        if (d) {
          this.$state.go('menu')
        }
      })
    }
  }

  isValid() {
    this.requiredErrors = []
    this.duplicatedErrors = []

    if (this.template) {
      const titleRegex = new RegExp('%clacoform_entry_title%', 'g')
      const titleMatches = this.template.match(titleRegex)

      if (titleMatches === null) {
        this.requiredErrors.push({name: 'clacoform_entry_title'})
      } else if (titleMatches.length > 1) {
        this.duplicatedErrors.push({name: 'clacoform_entry_title'})
      }
      this.mandatory.forEach(f => {
        const regex = new RegExp(`%${this.removeAccent(this.removeQuote(f['name']))}%`, 'g')
        const matches = this.template.match(regex)

        if (matches === null) {
          this.requiredErrors.push(f)
        } else if (matches.length > 1) {
          this.duplicatedErrors.push(f)
        }
      })
      this.optional.forEach(f => {
        const regex = new RegExp(`%${this.removeAccent(this.removeQuote(f['name']))}%`, 'g')
        const matches = this.template.match(regex)

        if (matches !== null && matches.length > 1) {
          this.duplicatedErrors.push(f)
        }
      })
    }

    return this.requiredErrors.length === 0 && this.duplicatedErrors.length === 0
  }

  removeQuote(str) {
    return str.replace(/'/g, ' ')
  }

  removeAccent(str) {
    return this.ClacoFormService.removeAccent(str)
  }
}