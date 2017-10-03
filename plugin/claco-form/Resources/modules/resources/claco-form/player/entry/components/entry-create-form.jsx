import React, {Component} from 'react'
import cloneDeep from 'lodash/cloneDeep'
import {connect} from 'react-redux'
import {withRouter} from 'react-router-dom'
import {PropTypes as T} from 'prop-types'
import {trans, t} from '#/main/core/translation'
import {FormField} from '#/main/core/layout/form/components/form-field.jsx'
import {SelectInput} from '#/main/core/layout/form/components/field/select-input.jsx'
import {getFieldType} from '../../../utils'
import {selectors} from '../../../selectors'
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

class EntryCreateForm extends Component {
  constructor(props) {
    super(props)
    const fieldsValues = {
      entry_title: ''
    }
    const errors = {
      entry_title: ''
    }
    props.fields.map(f => {
      fieldsValues[f.id] = getFieldType(f.type).answerType === 'array' ? [] : ''
      errors[f.id] = ''
    })
    this.state = {
      id: null,
      entry: fieldsValues,
      keywords: [],
      hasError: false,
      errors: errors,
      showKeywordForm: false,
      currentKeyword: ''
    }
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
    return field.locked && !field.lockedEditionOnly
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
    this.setState({entry: Object.assign({}, this.state.entry, {[property]: value})})
  }

  registerEntry() {
    if (!this.state['hasError']) {
      this.props.createEntry(this.state.entry, this.state.keywords)
      this.props.history.push('/menu')
    }
  }

  validateEntry() {
    let hasError = false
    const errors = cloneDeep(this.state.errors)
    errors['entry_title'] = this.state.entry.entry_title === '' ? trans('form_not_blank_error', {}, 'clacoform') : ''
    this.props.fields.forEach(f => {
      errors[f.id] = f.required && (this.state.entry[f.id] === '' || this.state.entry[f.id].length === 0 || !this.isValidCascade(this.state.entry[f.id])) ?
        trans('form_not_blank_error', {}, 'clacoform') :
        ''
    })
    Object.values(errors).forEach(e => {
      if (e) {
        hasError = true
      }
    })
    this.setState({errors: errors, hasError: hasError}, this.registerEntry)
  }

  render() {
    return (
      <div>
        <h2>{trans('entry_addition', {}, 'clacoform')}</h2>
        <br/>
        {this.props.canAddEntry ?
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
                error={this.state.errors[f.id]}
                onChange={value => this.updateEntryValue(f.id, value)}
              />
            )}
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
            <hr/>
            <div className="entry-form-buttons">
              <button className="btn btn-primary" onClick={() => this.validateEntry()}>
                <span>{t('ok')}</span>
              </button>
              <a href="#/menu" className="btn btn-default">
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

EntryCreateForm.propTypes = {
  canEdit: T.bool.isRequired,
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
  isKeywordsEnabled: T.bool.isRequired,
  isNewKeywordsEnabled: T.bool.isRequired,
  template: T.string,
  useTemplate: T.bool.isRequired,
  canAddEntry: T.bool.isRequired,
  createEntry: T.func.isRequired,
  history: T.object.isRequired
}

function mapStateToProps(state) {
  return {
    canEdit: state.canEdit,
    fields: selectors.visibleFields(state),
    isKeywordsEnabled: selectors.getParam(state, 'keywords_enabled'),
    isNewKeywordsEnabled: selectors.getParam(state, 'new_keywords_enabled'),
    keywords: selectors.getParam(state, 'keywords_enabled') ? state.keywords : [],
    canAddEntry: selectors.canAddEntry(state),
    useTemplate: selectors.getParam(state, 'use_template'),
    template: selectors.template(state)
  }
}

function mapDispatchToProps(dispatch) {
  return {
    createEntry: (entry, keywords) => dispatch(actions.createEntry(entry, keywords))
  }
}

const ConnectedEntryCreateForm = withRouter(connect(mapStateToProps, mapDispatchToProps)(EntryCreateForm))

export {ConnectedEntryCreateForm as EntryCreateForm}