import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import parse from 'html-react-parser'
import cloneDeep from 'lodash/cloneDeep'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'
import set from 'lodash/set'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {Form} from '#/main/app/content/form/components/form'
import {DataInput} from '#/main/app/data/components/input'
import {formatSections} from '#/main/app/content/form/parameters/utils'

import {selectors} from '#/plugin/claco-form/resources/claco-form/store'
import {
  Field as FieldType,
  Entry as EntryType
} from '#/plugin/claco-form/resources/claco-form/prop-types'
import {EntryFormData} from '#/plugin/claco-form/resources/claco-form/player/components/entry-form-data'
import {ResourcePage} from '#/main/core/resource'
import {PageContent, PageSection} from '#/main/app/page'
import {FormContent} from '#/main/app/content/form/containers/content'
import {ContentError} from '#/main/app/content/error'

const EntryCategories = (props) => {
  return (
    <EntryFormData
      data={props.entryCategories}
      choices={props.categories}
      onAdd={props.onAdd}
      onRemove={props.onRemove}
    />
  )
}

EntryCategories.propTypes = {
  entryCategories: T.array,
  categories: T.array,
  onAdd: T.func.isRequired,
  onRemove: T.func.isRequired
}

class EntryForm extends Component {
  constructor(props) {
    super(props)

    this.state = {
      template: ''
    }
  }

  componentDidMount() {
    if (this.props.useTemplate && this.props.template) {
      this.generateTemplate()
    }
  }

  // for standard form
  getSections() {
    const hasConfidentialRights = this.props.canAdministrate

    const hasLockedRights = this.props.canAdministrate

    // generate form based on claco-form defined fields
    const formSections = formatSections([
      {
        id: 'general',
        title: trans('general'),
        primary: true,
        fields: this.props.fields.map(field => {
          const fieldDef = cloneDeep(field)

          if ('file' === fieldDef.type) {
            fieldDef.options = Object.assign({}, fieldDef.options, {
              uploadUrl: ['apiv2_clacoformentry_file_upload', {clacoForm: this.props.clacoFormId}]
            })
          }

          return fieldDef
        })
      }
    ], this.props.fields, 'values', this.props.entry.user && this.props.entry.user.id === this.props.currentUser.id, hasConfidentialRights, hasLockedRights)

    // add entry title to the generated form
    formSections[0].fields = [
      {
        name: 'title',
        type: 'string',
        label: this.props.titleLabel ? this.props.titleLabel : trans('title'),
        required: true
      }
    ].concat(formSections[0].fields)

    return formSections
  }

  generateTemplate() {
    let template = this.props.template
    template = template.replace('%clacoform_entry_title%', '<span class="clacoform-field" id="clacoform-field-title"></span>')
    this.props.fields.forEach(f => {
      template = template.replace(`%field_${f.id}%`, `<span class="clacoform-field" id="clacoform-field-${f.id}"></span>`)
    })

    this.setState({template: template})
  }

  // for template
  getFields() {
    // generate field list for template
    return [
      // title field
      {
        id: 'title',
        type: 'string',
        label: trans('title'),
        required: true,
        hideLabel: true,
        value: this.props.entry.title,
        error: this.props.errors.title,
        onChange: (value) => this.props.updateFormProp('title', value),
        onError: (errors) => {
          const newErrors = this.props.errors ? cloneDeep(this.props.errors) : {}
          set(newErrors, 'title', errors)

          this.props.setErrors(newErrors)
        }
      }
    ].concat(this.props.fields.map(field => {
      // remap some options to make it work with forms
      let options = field.options ? Object.assign({}, field.options) : {}

      if (field.type === 'choice') {
        const choices = options.choices ?
          options.choices.reduce((acc, choice) => Object.assign(acc, {
            [choice.value]: choice.value
          }), {}) : {}

        options = Object.assign({}, options, {choices: choices})
      }

      if (field.type === 'file') {
        options.uploadUrl = ['apiv2_clacoformentry_file_upload', {clacoForm: this.props.clacoFormId}]
      }

      return {
        id: field.id,
        type: field.type,
        label: field.label,
        required: field.required,
        disabled: !this.props.canAdministrate && ((this.props.isNew && field.restrictions.locked && !field.restrictions.lockedEditionOnly) || (!this.props.isNew && field.restrictions.locked)),
        help: field.help,
        hideLabel: true,
        value: this.props.entry.values ? this.props.entry.values[field.id] : undefined,
        error: get(this.props.errors, `values.${field.id}`),
        options: options,
        onChange: (value) => this.props.updateFormProp(`values.${field.id}`, value),
        onError: (errors) => {
          const newErrors = this.props.errors ? cloneDeep(this.props.errors) : {}
          set(newErrors, `values.${field.id}`, errors)

          this.props.setErrors(newErrors)
        }
      }
    }))
  }

  getConfirm() {
    if (this.props.isNew && this.props.showConfirm) {
      return {
        icon: '',
        title: trans('confirm_new_entry', {}, 'clacoform'),
        message: this.props.confirmMessage ? this.props.confirmMessage : trans('confirm_new_entry_message', {}, 'clacoform')
      }
    }

    return undefined
  }

  render() {
    if (this.props.isNew && !this.props.canAddEntry) {
      return (
        <ResourcePage>
          <PageContent className="d-flex flex-column">
            <PageSection className="py-5 my-auto">
              <ContentError
                title={trans('entry_creation_not_allowed', {}, 'clacoform')}
                description={trans('entry_creation_not_allowed_desc', {contentName: `<b>${this.props.resourceName}</b>`}, 'clacoform')}
                help={trans('entry_creation_not_allowed_contact', {contactLink: this.props.contactEmail ?
                  `(<a href="mailto:${this.props.contactEmail}">${this.props.contactEmail}</a>)` : ''
                }, 'clacoform')}
                backAction={{
                  type: LINK_BUTTON,
                  label: trans('back_home', {}, 'actions'),
                  target: this.props.path,
                  exact: true
                }}
              />
            </PageSection>
          </PageContent>
        </ResourcePage>
      )
    }

    const fields = this.getFields()

    return (
      <ResourcePage>
        <PageContent className="d-flex flex-column">
          <Form
            className="mt-5 flex-fill"
            pendingChanges={this.props.pendingChanges}
            errors={!isEmpty(this.props.errors)}
            save={{
              type: CALLBACK_BUTTON,
              callback: () => this.props.saveForm(this.props.entry, this.props.fields, this.props.isNew, this.props.history.push, this.props.path),
              confirm: this.getConfirm()
            }}
            cancel={{
              type: LINK_BUTTON,
              target: this.props.entry.id ? `${this.props.path}/entries/${this.props.entry.id}` : this.props.path,
              exact: true
            }}
          >
            {(this.props.useTemplate && this.props.template) &&
              parse(this.state.template, {
                replace: (element) => {
                  if (element.attribs && element.attribs.class === 'clacoform-field' && element.attribs.id) {
                    // this is a field, replace it with a form input
                    // get the field ID and retrieve it
                    const id = element.attribs.id.replace('clacoform-field-', '')
                    const field = fields.find(f => f.id === id)
                    if (field) {
                      return (
                        <DataInput
                          id={`field-${field.id}`}
                          {...field}
                        />
                      )
                    }
                  }

                  return element
                }
              })
            }

            {(!this.props.useTemplate || !this.props.template) &&
              <FormContent
                level={3}
                name={selectors.STORE_NAME+'.entries.current'}
                definition={this.getSections()}
              />
            }

            {(this.props.canAdministrate && !isEmpty(this.props.categories)) &&
              <EntryCategories
                entryCategories={this.props.entry.categories}
                categories={this.props.categories}
                onAdd={this.props.addCategory}
                onRemove={(category) => this.props.removeCategory(category.id)}
              />
            }
          </Form>
        </PageContent>
      </ResourcePage>
    )
  }
}

EntryForm.propTypes = {
  path: T.string.isRequired,
  currentUser: T.object,
  contactEmail: T.string,
  resourceName: T.string.isRequired,
  canAdministrate: T.bool.isRequired,
  canAddEntry: T.bool.isRequired,
  clacoFormId: T.string.isRequired,
  fields: T.arrayOf(T.shape(FieldType.propTypes)).isRequired,
  template: T.string,
  useTemplate: T.bool.isRequired,
  titleLabel: T.string,
  displayMetadata: T.string.isRequired,
  isNew: T.bool.isRequired,
  errors: T.object,
  entry: T.shape(EntryType.propTypes),
  categories: T.array,
  saveForm: T.func.isRequired,
  updateFormProp: T.func.isRequired,
  setErrors: T.func.isRequired,
  addCategory: T.func.isRequired,
  removeCategory: T.func.isRequired,
  history: T.object.isRequired,
  pendingChanges: T.bool.isRequired,
  showConfirm: T.bool.isRequired,
  confirmMessage: T.string
}

export {
  EntryForm
}
