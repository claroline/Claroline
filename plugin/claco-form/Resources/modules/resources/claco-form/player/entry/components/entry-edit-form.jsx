import React, {Component} from 'react'
import ReactDOM from 'react-dom'
import cloneDeep from 'lodash/cloneDeep'
import {connect} from 'react-redux'
import {withRouter} from 'react-router-dom'
import {PropTypes as T} from 'prop-types'
import {trans, t} from '#/main/core/translation'
import {generateUrl} from '#/main/core/fos-js-router'
import {FormField} from '#/main/core/layout/form/components/form-field.jsx'
import {SelectInput} from '#/main/core/layout/form/components/field/select-input.jsx'
import {HtmlText} from '#/main/core/layout/components/html-text.jsx'
import {getFieldType} from '../../../utils'
import {selectors} from '../../../selectors'
import {select as resourceSelect} from '#/main/core/layout/resource/selectors'
import {actions} from '../actions'

const InfosList = props =>
  <span className="entry-form-infos-list">
    {props.infos.map(info =>
      <div key={info} className="btn-group margin-right-sm margin-bottom-sm">
          <button className="btn btn-default">
            {info}
          </button>
          <button
            className="btn btn-danger"
            onClick={() => props.removeInfo(info)}
          >
              <span className="fa fa-times-circle"></span>
          </button>
      </div>
    )}
  </span>

InfosList.propTypes = {
  infos: T.arrayOf(T.string).isRequired,
  removeInfo: T.func.isRequired
}

class EntryEditForm extends Component {
  constructor(props) {
    super(props)
    const errors = {
      entry_title: ''
    }
    props.fields.map(f => {
      errors[f.id] = ''
    })
    this.state = {
      entry: {},
      categories: [],
      keywords: [],
      files: {},
      hasError: false,
      errors: errors,
      showCategoryForm: false,
      showKeywordForm: false,
      currentCategory: '',
      currentKeyword: ''
    }
  }

  componentDidMount() {
    this.loadEntry()
  }

  componentDidUpdate(prevProps) {
    if (prevProps.entryId !== this.props.entryId) {
      this.loadEntry()
    }
  }

  loadEntry() {
    let entry = this.props.entries.find(e => e.id === this.props.entryId)

    if (entry) {
      this.initializeEntry(entry)
    } else {
      fetch(
        generateUrl('claro_claco_form_entry_retrieve', {entry: this.props.entryId}),
        {method: 'GET', credentials: 'include'}
      )
      .then(response => response.json())
      .then(results => this.initializeEntry(results))
    }
  }

  initializeEntry(entry) {
    const categories = entry.categories ? entry.categories.map(c => c.name) : []
    const keywords = entry.keywords ? entry.keywords.map(k => k.name) : []
    const values = {entry_title: entry.title}
    this.props.fields.map(f => {
      const fieldValue = entry.fieldValues ? entry.fieldValues.find(fv => fv.field.id === f.id) : null

      if (fieldValue && fieldValue.fieldFacetValue) {
        values[f.id] = fieldValue.fieldFacetValue.value
      } else {
        values[f.id] = getFieldType(f.type).answerType === 'array' ? [] : ''
      }
    })

    this.setState(
      {id: this.props.entryId, entry: values, keywords: keywords, categories: categories},
      //() => this.renderTemplateFields()
      () => setTimeout(() => this.renderTemplateFields(), 1000)
    )
    this.props.setCurrentEntry(entry)
  }

  generateTemplate() {
    let template = this.props.template
    template = template.replace('%clacoform_entry_title%', '<span id="clacoform-entry-title"></span>')
    this.props.fields.filter(f => f.type !== 11).forEach(f => {
      template = template.replace(`%field_${f.id}%`, `<span id="clacoform-field-${f.id}"></span>`)
    })

    return template
  }

  renderTemplateFields() {
    if (this.props.template && this.props.useTemplate) {
      const title =
        <FormField
          controlId="field-title"
          type="text"
          label={t('title')}
          value={this.state.entry.entry_title}
          error={this.state.errors.entry_title}
          onChange={value => this.updateEntryValue('entry_title', value)}
        />
      const element = document.getElementById('clacoform-entry-title')

      if (element) {
        ReactDOM.render(title, element)
      }
      this.props.fields.filter(f => f.type !== 11).forEach(f => {
        const fieldEl = document.getElementById(`clacoform-field-${f.id}`)

        if (fieldEl) {
          const fieldComponent =
            <FormField
              key={`field-${f.id}`}
              controlId={`field-${f.id}`}
              type={getFieldType(f.type).name}
              label={f.name}
              disabled={this.isFieldLocked(f)}
              noLabel={true}
              choices={f.fieldFacet ?
                f.fieldFacet.field_facet_choices.map(ffc => Object.assign({}, ffc, {value: ffc.label})) :
                []
              }
              value={this.state.entry[f.id]}
              error={this.state.errors[f.id]}
              onChange={value => this.updateEntryValue(f.id, value)}
            />
          ReactDOM.render(fieldComponent, fieldEl)
        }
      })
    }
  }

  addCategory() {
    if (this.state.currentCategory) {
      const categories = cloneDeep(this.state.categories)
      const index = categories.findIndex(c => c === this.state.currentCategory)

      if (index < 0) {
        categories.push(this.state.currentCategory)
      }
      this.setState({categories: categories, currentCategory: '', showCategoryForm: false})
    }
  }

  removeCategory(category) {
    const categories = cloneDeep(this.state.categories)
    const index = categories.findIndex(c => c === category)

    if (index >= 0) {
      categories.splice(index, 1)
    }
    this.setState({categories: categories})
  }

  addKeyword() {
    if (this.state.currentKeyword) {
      const keywords = cloneDeep(this.state.keywords)
      const index = keywords.findIndex(k => k.toUpperCase() === this.state.currentKeyword.toUpperCase())

      if (index < 0) {
        keywords.push(this.state.currentKeyword)
      }
      this.setState({keywords: keywords, currentKeyword: '', showKeywordForm: false})
    }
  }

  removeKeyword(keyword) {
    const keywords = cloneDeep(this.state.keywords)
    const index = keywords.findIndex(k => k === keyword)

    if (index >= 0) {
      keywords.splice(index, 1)
    }
    this.setState({keywords: keywords})
  }

  isFieldLocked(field) {
    return !this.props.canEdit && field.locked && (
      (['user', 'all'].indexOf(this.props.lockedFieldsFor) > -1 && !this.props.isManager) ||
      (['manager', 'all'].indexOf(this.props.lockedFieldsFor) > -1 && !this.props.isOwner)
    )
  }

  isValidCascade(value) {
    let isValid = true

    if (Array.isArray(value)) {
      value.forEach(v => {
        if (v === '') {
          isValid = false
        }
      })
    }

    return isValid
  }

  updateEntryValue(property, value) {
    this.setState({entry: Object.assign({}, this.state.entry, {[property]: value})}, this.renderTemplateFields)
  }

  registerEntry() {
    if (!this.state['hasError']) {
      this.props.editEntry(
        this.state.id,
        this.state.entry,
        this.state.keywords,
        this.state.categories.map(categoryName => this.props.categories.find(c => c.name === categoryName).id),
        this.state.files
      )
      this.props.history.push(`/entry/${this.state.id}/view`)
    } else {
      this.renderTemplateFields()
    }
  }

  validateEntry() {
    let hasError = false
    const errors = cloneDeep(this.state.errors)
    errors['entry_title'] = this.state.entry.entry_title === '' ? trans('form_not_blank_error', {}, 'clacoform') : ''
    this.props.fields.forEach(f => {
      errors[f.id] = f.required &&
        (
          this.state.entry[f.id] === undefined ||
          this.state.entry[f.id] === null ||
          this.state.entry[f.id] === '' ||
          (Array.isArray(this.state.entry[f.id]) && this.state.entry[f.id].length === 0)  ||
          !this.isValidCascade(this.state.entry[f.id])
        ) ?
          trans('form_not_blank_error', {}, 'clacoform') :
          ''
    })
    Object.values(errors).forEach(e => {
      if (e) {
        hasError = true
      }
    })
    const files = {}

    this.props.fields.forEach(f => {
      if (getFieldType(f.type).name === 'file') {
        if (this.state.entry[f.id]) {
          if (!files[f.id]) {
            files[f.id] = []
          }

          this.state.entry[f.id].forEach(file => {
            if (!file.url) {
              files[f.id].push(file)
            }
          })
        }
      }
    })
    this.setState({errors: errors, hasError: hasError, files: files}, this.registerEntry)
  }

  render() {
    return (
      <div>
        <h2>{trans('entry_edition', {}, 'clacoform')}</h2>
        <br/>
        {this.props.entry && this.props.entry.id > 0 && this.state.id > 0 && this.props.canEditEntry ?
          <div>
            {this.props.template && this.props.useTemplate ?
              <HtmlText>
                {this.generateTemplate()}
              </HtmlText> :
              <div>
                <FormField
                  controlId="field-title"
                  type="text"
                  label={t('title')}
                  value={this.state.entry.entry_title}
                  error={this.state.errors.entry_title}
                  onChange={value => this.updateEntryValue('entry_title', value)}
                />
                {this.props.fields.map(f =>
                  <FormField
                    key={`field-${f.id}`}
                    controlId={`field-${f.id}`}
                    type={getFieldType(f.type).name}
                    label={f.name}
                    disabled={this.isFieldLocked(f)}
                    noLabel={false}
                    choices={f.fieldFacet ?
                      f.fieldFacet.field_facet_choices.map(ffc => Object.assign({}, ffc, {value: ffc.label})) :
                      []
                    }
                    value={this.state.entry[f.id]}
                    max={f.details && !isNaN(f.details.nb_files_max) ? parseInt(f.details.nb_files_max) : undefined}
                    types={f.details && f.details.file_types ? f.details.file_types : []}
                    error={this.state.errors[f.id]}
                    onChange={value => this.updateEntryValue(f.id, value)}
                  />
                )}
              </div>
            }
            {this.props.isKeywordsEnabled &&
              <div>
                <hr/>
                <h3>{trans('keywords', {}, 'clacoform')}</h3>
                {this.state.keywords.length > 0 &&
                  <InfosList
                    infos={this.state.keywords}
                    removeInfo={keyword => this.removeKeyword(keyword)}
                  />
                }
                {this.state.showKeywordForm ?
                  <SelectInput
                    selectMode={!this.props.isNewKeywordsEnabled}
                    options={this.props.keywords.map(k => {
                      return {value: k.name, label: k.name}
                    })}
                    primaryLabel={t('add')}
                    disablePrimary={!this.state.currentKeyword}
                    typeAhead={this.props.isNewKeywordsEnabled}
                    value={this.state.currentKeyword}
                    onChange={value => this.setState({currentKeyword: value})}
                    onPrimary={() => this.addKeyword()}
                    onSecondary={() => {
                      this.setState({showKeywordForm: false, currentKeyword: ''})
                    }}
                  /> :
                  <button
                    className="btn btn-default margin-bottom-sm"
                    onClick={() => this.setState({showKeywordForm: true, currentKeyword: ''})}
                  >
                      <span className="fa fa-w fa-plus"></span>
                  </button>
                }
              </div>
            }
            {(this.props.canEdit || this.props.isManager) &&
              <div>
                <hr/>
                <h3>{t('categories')}</h3>
                {this.state.categories.length > 0 &&
                  <InfosList
                    infos={this.state.categories}
                    removeInfo={category => this.removeCategory(category)}
                  />
                }
                {this.state.showCategoryForm ?
                  <SelectInput
                    selectMode={true}
                    options={this.props.categories.map(c => {
                      return {value: c.name, label: c.name}
                    })}
                    primaryLabel={t('add')}
                    disablePrimary={!this.state.currentCategory}
                    value={this.state.currentKeyword}
                    onChange={value => this.setState({currentCategory: value})}
                    onPrimary={() => this.addCategory()}
                    onSecondary={() => {
                      this.setState({showCategoryForm: false, currentCategory: ''})
                    }}
                  /> :
                  <button
                    className="btn btn-default margin-bottom-sm"
                    onClick={() => this.setState({showCategoryForm: true, currentCategory: ''})}
                  >
                      <span className="fa fa-w fa-plus" />
                  </button>
                }
              </div>
            }
            <hr/>
            <div className="entry-form-buttons">
              <button className="btn btn-primary" onClick={() => this.validateEntry()}>
                <span>{t('ok')}</span>
              </button>
              <a href={`#/entry/${this.props.entryId}/view`} className="btn btn-default">
                {t('cancel')}
              </a>
            </div>
          </div> :
          <div className="alert alert-danger">
            {t('unauthorized')}
          </div>
        }
      </div>
    )
  }
}

EntryEditForm.propTypes = {
  entryId: T.number,
  canEdit: T.bool.isRequired,
  entry: T.shape({
    id: T.number
  }),
  fields: T.arrayOf(T.shape({
    id: T.number.isRequired,
    type: T.number.isRequired,
    name: T.string.isRequired,
    locked: T.bool.isRequired,
    lockedEditionOnly: T.bool.isRequired,
    required: T.bool,
    isMetadata: T.bool,
    hidden: T.bool,
    fieldFacet: T.shape({
      id: T.number.isRequired,
      name: T.string.isRequired,
      type: T.number.isRequired,
      field_facet_choices: T.arrayOf(T.shape({
        id: T.number.isRequired,
        label: T.string.isRequired,
        parent: T.shape({
          id: T.number.isRequired,
          label: T.string.isRequired
        })
      }))
    })
  })),
  keywords: T.arrayOf(T.shape({
    id: T.number.isRequired,
    name: T.string.isRequired
  })),
  categories: T.arrayOf(T.shape({
    id: T.number.isRequired,
    name: T.string.isRequired
  })),
  entries: T.arrayOf(T.shape({
    id: T.number.isRequired
  })),
  isKeywordsEnabled: T.bool.isRequired,
  isNewKeywordsEnabled: T.bool.isRequired,
  lockedFieldsFor: T.string.isRequired,
  canEditEntry: T.bool.isRequired,
  isManager: T.bool,
  isOwner: T.bool,
  template: T.string,
  useTemplate: T.bool.isRequired,
  editEntry: T.func.isRequired,
  setCurrentEntry: T.func.isRequired,
  history: T.object.isRequired
}

function mapStateToProps(state, ownProps) {
  return {
    entryId: ownProps.match.params.id ? parseInt(ownProps.match.params.id) : null,
    canEdit: resourceSelect.editable(state),
    entry: state.currentEntry,
    fields: selectors.visibleFields(state),
    entries: state.entries.data,
    isKeywordsEnabled: selectors.getParam(state, 'keywords_enabled'),
    isNewKeywordsEnabled: selectors.getParam(state, 'new_keywords_enabled'),
    lockedFieldsFor: selectors.getParam(state, 'locked_fields_for'),
    keywords: selectors.getParam(state, 'keywords_enabled') ? state.keywords : [],
    categories: resourceSelect.editable(state) ? state.categories : [],
    canEditEntry: selectors.canEditCurrentEntry(state),
    isManager: selectors.isCurrentEntryManager(state),
    isOwner: selectors.isCurrentEntryOwner(state),
    useTemplate: selectors.getParam(state, 'use_template'),
    template: selectors.template(state)
  }
}

function mapDispatchToProps(dispatch) {
  return {
    setCurrentEntry: (entry) => dispatch(actions.loadCurrentEntry(entry)),
    editEntry: (entryId, entry, keywords, categories, files) => dispatch(actions.editEntry(entryId, entry, keywords, categories, files))
  }
}

const ConnectedEntryEditForm = withRouter(connect(mapStateToProps, mapDispatchToProps)(EntryEditForm))

export {ConnectedEntryEditForm as EntryEditForm}