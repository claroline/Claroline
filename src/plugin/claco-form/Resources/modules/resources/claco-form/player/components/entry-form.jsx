import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import parse from 'html-react-parser'
import cloneDeep from 'lodash/cloneDeep'
import isEmpty from 'lodash/isEmpty'
import set from 'lodash/set'

import {withRouter} from '#/main/app/router'
import {Alert} from '#/main/app/alert/components/alert'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {actions as formActions} from '#/main/app/content/form/store/actions'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {notEmpty} from '#/main/app/data/types/validators'
import {FormData} from '#/main/app/content/form/containers/data'
import {Form} from '#/main/app/content/form/components/form'
import {DataInput} from '#/main/app/data/components/input'
import {formatField, isFieldDisplayed} from '#/main/core/user/profile/utils'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {selectors} from '#/plugin/claco-form/resources/claco-form/store'
import {
  Field as FieldType,
  Entry as EntryType,
  EntryUser as EntryUserType
} from '#/plugin/claco-form/resources/claco-form/prop-types'
import {actions} from '#/plugin/claco-form/resources/claco-form/player/store'
import {EntryFormData} from '#/plugin/claco-form/resources/claco-form/player/components/entry-form-data'

// TODO : split template form and standard form in 2 different components

class EntryFormComponent extends Component {
  constructor(props) {
    super(props)

    this.state = {
      template: ''
    }

    //this.getConfirm = this.getConfirm.bind(this)
  }

  componentDidMount() {
    if (this.props.useTemplate && this.props.template) {
      this.generateTemplate()
    }
  }

  // for standard form
  getSections() {
    const isShared = this.props.entryUser && this.props.entryUser.id ? this.props.entryUser.shared : false

    const sectionFields = [
      {
        name: 'title',
        type: 'string',
        label: this.props.titleLabel ? this.props.titleLabel : trans('title'),
        required: true
      }
    ]
    this.props.fields
      .filter(f => this.props.isNew ||
        isShared ||
        this.props.displayMetadata === 'all' ||
        (this.props.displayMetadata === 'manager' && this.props.isManager) ||
        !f.restrictions.metadata ||
        (this.props.entry.user && this.props.entry.user.id === this.props.currentUser.id)
      )
      .forEach(f => {
        const params = formatField(f, this.props.fields, 'values')

        params.disabled = !this.props.isManager
          && ((this.props.isNew && f.restrictions.locked && !f.restrictions.lockedEditionOnly) || (!this.props.isNew && f.restrictions.locked))

        switch (f.type) {
          case 'file':
            params['options'] = Object.assign({}, params['options'], {'uploadUrl': ['apiv2_clacoformentry_file_upload', {clacoForm: this.props.clacoFormId}]})
            break
        }
        sectionFields.push(params)
      })

    return [
      {
        id: 'general',
        title: trans('general'),
        primary: true,
        fields: sectionFields
      }
    ]
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
        label: field.name,
        required: field.required,
        disabled: !this.props.isManager && ((this.props.isNew && field.restrictions.locked && !field.restrictions.lockedEditionOnly) || (!this.props.isNew && field.restrictions.locked)),
        help: field.help,
        hideLabel: true,
        value: this.props.entry.values ? this.props.entry.values[field.id] : undefined,
        error: this.props.errors[field.id],
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

  renderCategories() {
    if (this.props.canEdit || this.props.isManager || this.props.isKeywordsEnabled) {
      return (
        <FormSections level={3}>
          {(this.props.canEdit || this.props.isManager) &&
            <FormSection
              id="entry-categories"
              className="embedded-list-section"
              icon="fa fa-fw fa-table"
              title={trans('categories')}
            >
              <EntryFormData
                data={this.props.entry.categories}
                choices={this.props.categories}
                onAdd={(category) => this.props.addCategory(category)}
                onRemove={(category) => this.props.removeCategory(category.id)}
              />
            </FormSection>
          }
          {this.props.isKeywordsEnabled &&
            <FormSection
              id="entry-keywords"
              className="embedded-list-section"
              icon="fa fa-fw fa-font"
              title={trans('keywords', {}, 'clacoform')}
            >
              <EntryFormData
                data={this.props.entry.keywords}
                choices={this.props.keywords}
                allowNew={this.props.isNewKeywordsEnabled}
                onAdd={(keyword) => this.props.addKeyword(keyword)}
                onRemove={(keyword) => this.props.removeKeyword(keyword.id)}
              />
            </FormSection>
          }
        </FormSections>
      )
    }

    return null
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
        <Alert type="warning">
          {trans('entry_creation_not_allowed', {}, 'clacoform')}
        </Alert>
      )
    }

    const fields = this.getFields()

    return (
      <Fragment>
        {this.props.entry && (this.props.useTemplate && this.props.template) &&
          <Form
            pendingChanges={this.props.pendingChanges}
            errors={!isEmpty(this.props.errors)}
            validating={this.props.validating}
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
            <div className="panel panel-default">
              <div className="panel-body">
                {parse(this.state.template, {
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
                })}
              </div>
            </div>

            {this.renderCategories()}
          </Form>
        }

        {this.props.entry && (!this.props.useTemplate || !this.props.template) &&
          <FormData
            level={3}
            buttons={true}
            name={selectors.STORE_NAME+'.entries.current'}
            sections={this.getSections()}
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
            {this.renderCategories()}
          </FormData>
        }
      </Fragment>
    )
  }
}

EntryFormComponent.propTypes = {
  path: T.string.isRequired,
  currentUser: T.object,
  canEdit: T.bool.isRequired,
  canAddEntry: T.bool.isRequired,
  clacoFormId: T.string.isRequired,
  fields: T.arrayOf(T.shape(FieldType.propTypes)).isRequired,
  template: T.string,
  useTemplate: T.bool.isRequired,
  titleLabel: T.string,
  displayMetadata: T.string.isRequired,
  isKeywordsEnabled: T.bool.isRequired,
  isNewKeywordsEnabled: T.bool.isRequired,
  isManager: T.bool.isRequired,
  isNew: T.bool.isRequired,
  errors: T.object,
  entry: T.shape(EntryType.propTypes),
  entryUser: T.shape(EntryUserType.propTypes),
  categories: T.array,
  keywords: T.array,
  saveForm: T.func.isRequired,
  updateFormProp: T.func.isRequired,
  setErrors: T.func.isRequired,
  addCategory: T.func.isRequired,
  removeCategory: T.func.isRequired,
  addKeyword: T.func.isRequired,
  removeKeyword: T.func.isRequired,
  history: T.object.isRequired,
  pendingChanges: T.bool.isRequired,
  validating: T.bool.isRequired,
  showConfirm: T.bool.isRequired,
  confirmMessage: T.string
}

const EntryForm = withRouter(connect(
  state => ({
    currentUser: securitySelectors.currentUser(state),
    path: resourceSelectors.path(state),

    canAddEntry: selectors.canAddEntry(state),
    canEdit: hasPermission('edit', resourceSelectors.resourceNode(state)),
    clacoFormId: selectors.clacoForm(state).id,
    fields: selectors.visibleFields(state),
    useTemplate: selectors.useTemplate(state),
    template: selectors.template(state),
    showConfirm: selectors.showConfirm(state),
    confirmMessage: selectors.confirmMessage(state),
    titleLabel: selectors.params(state).title_field_label,
    displayMetadata: selectors.params(state).display_metadata,
    isKeywordsEnabled: selectors.params(state).keywords_enabled,
    isNewKeywordsEnabled: selectors.params(state).new_keywords_enabled,
    isManager: selectors.isCurrentEntryManager(state),
    isNew: formSelect.isNew(formSelect.form(state, selectors.STORE_NAME+'.entries.current')),
    errors: formSelect.errors(formSelect.form(state, selectors.STORE_NAME+'.entries.current')),
    entry: formSelect.data(formSelect.form(state, selectors.STORE_NAME+'.entries.current')),
    pendingChanges: formSelect.pendingChanges(formSelect.form(state, selectors.STORE_NAME+'.entries.current')),
    validating: formSelect.validating(formSelect.form(state, selectors.STORE_NAME+'.entries.current')),
    entryUser: selectors.entryUser(state),
    categories: selectors.categories(state),
    keywords: selectors.keywords(state)
  }),
  (dispatch) => ({
    saveForm(entry, fields, isNew, navigate, path) {
      // validate required fields
      // TODO : this should be done by standard form validation (it's broken atm)
      const errors = {
        title: notEmpty(entry.title),
        values: {}
      }
      const requiredFields = fields.filter(field => field.required && isFieldDisplayed(field, fields, entry.values))
      errors.values = requiredFields.reduce((fieldErrors, field) => Object.assign(fieldErrors, {
        [field.id]: notEmpty(entry.values[field.id])
      }), {})

      dispatch(formActions.setErrors(selectors.STORE_NAME+'.entries.current', errors))

      if (isNew) {
        dispatch(formActions.saveForm(selectors.STORE_NAME+'.entries.current', ['apiv2_clacoformentry_create'])).then((data) => {
          dispatch(actions.addCreatedEntry(data))
          navigate(`${path}/entries/${data.id}`)
        }, () => true)
      } else {
        dispatch(formActions.saveForm(selectors.STORE_NAME+'.entries.current', ['apiv2_clacoformentry_update', {id: entry.id}]))
      }
    },
    updateFormProp(propName, propValue) {
      dispatch(formActions.updateProp(selectors.STORE_NAME+'.entries.current', propName, propValue))
    },
    setErrors(errors) {
      dispatch(formActions.setErrors(selectors.STORE_NAME+'.entries.current', errors))
    },
    addCategory(category) {
      dispatch(actions.addCategory(category))
    },
    removeCategory(categoryId) {
      dispatch(actions.removeCategory(categoryId))
    },
    addKeyword(keyword) {
      dispatch(actions.addKeyword(keyword))
    },
    removeKeyword(keywordId) {
      dispatch(actions.removeKeyword(keywordId))
    }
  })
)(EntryFormComponent))

export {
  EntryForm
}