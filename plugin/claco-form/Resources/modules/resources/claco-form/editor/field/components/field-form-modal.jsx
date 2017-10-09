import cloneDeep from 'lodash/cloneDeep'
import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import Modal from 'react-bootstrap/lib/Modal'
import classes from 'classnames'
import {BaseModal} from '#/main/core/layout/modal/components/base.jsx'
import {CheckGroup} from '#/main/core/layout/form/components/group/check-group.jsx'
import {t, trans} from '#/main/core/translation'
import {generateUrl} from '#/main/core/fos-js-router'
import {NumberGroup} from '#/main/core/layout/form/components/group/number-group.jsx'
import {SelectGroup} from '#/main/core/layout/form/components/group/select-group.jsx'
import {constants} from '../../../constants'
import {getFieldType} from '../../../utils'
import {ChoiceField} from './choice-field.jsx'

export const MODAL_FIELD_FORM = 'MODAL_FIELD_FORM'

class FieldFormModal  extends Component {
  constructor(props) {
    super(props)
    let choiceIndex = 2

    if (props.field.fieldFacet && props.field.fieldFacet.field_facet_choices.length > 0) {
      props.field.fieldFacet.field_facet_choices.forEach(ffc => {
        if (ffc.id >= choiceIndex) {
          choiceIndex = ffc.id + 1
        }
      })
    }
    this.state = {
      isFetching : false,
      hasError: false,
      nameError: null,
      field : {
        id: props.field.id,
        name: props.field.name,
        type: props.field.type,
        required: props.field.required,
        isMetadata: props.field.isMetadata,
        locked: props.field.locked,
        lockedEditionOnly: props.field.lockedEditionOnly,
        hidden: props.field.hidden,
        details: {
          file_types: props.field.details && props.field.details.file_types ? props.field.details.file_types : [],
          nb_files_max: props.field.details && props.field.details.nb_files_max ? props.field.details.nb_files_max : 1
        }
      },
      choices: props.field.fieldFacet && props.field.fieldFacet.field_facet_choices.length > 0 ?
        props.field.fieldFacet.field_facet_choices.filter(ffc => !ffc.parent).map(ffc => {return {
          index: ffc.id,
          value: ffc.label,
          new: false,
          category: null,
          error: ''
        }}) :
        [{
          index: 1,
          value: '',
          new: true,
          category: null,
          error: ''
        }],
      choicesChildren: {},
      choicesLoaded: !props.field.fieldFacet || props.field.fieldFacet.field_facet_choices.length === 0,
      choiceIndex: choiceIndex
    }
  }

  componentDidMount() {
    this.generateChoicesChildren()
  }

  generateChoicesChildren() {
    if (this.props.field.fieldFacet && this.props.field.fieldFacet.field_facet_choices.length > 0 ) {
      const choicesChildren = {}
      this.props.field.fieldFacet.field_facet_choices.filter(ffc => ffc.parent).forEach(ffc => {
        const parentId = ffc.parent.id

        if (!choicesChildren[parentId]) {
          choicesChildren[parentId] = []
        }
        choicesChildren[parentId].push({
          index: ffc.id,
          value: ffc.label,
          new: false,
          category: null,
          error: ''
        })
      })
      this.setState({choicesChildren: choicesChildren}, this.loadChoicesCategories)
    }
  }

  loadChoicesCategories() {
    if (this.props.field.fieldFacet && this.props.field.fieldFacet.field_facet_choices.length > 0) {
      fetch(
        generateUrl('claro_claco_form_field_choices_categories_retrieve', {field: this.props.field.id}),
        {
          method: 'GET' ,
          credentials: 'include'
        }
      )
      .then(response => response.json())
      .then(results => {
        if (results) {
          const choices = cloneDeep(this.state.choices)
          const choicesChildren = cloneDeep(this.state.choicesChildren)
          JSON.parse(results).forEach(data => {
            const idx = choices.findIndex(c => c.index === data.fieldFacetChoice.id)

            if (idx >= 0) {
              choices[idx] = Object.assign({}, choices[idx], {category: data.category.id})
            } else {
              for (let key in choicesChildren) {
                const childIdx = choicesChildren[key].findIndex(c => c.index === data.fieldFacetChoice.id)

                if (childIdx >= 0) {
                  choicesChildren[key][childIdx] = Object.assign({}, choicesChildren[key][childIdx], {category: data.category.id})
                  break
                }
              }
            }
          })
          this.setState(
            {choices: choices, choicesChildren: choicesChildren},
            () => this.setState({choicesLoaded: true})
          )
        } else {
          this.setState({choicesLoaded: true})
        }
      })
    }
  }

  checkFieldName() {
    if (this.state.field.name) {
      this.setState({isFetching: true})

      fetch(
        generateUrl(
          'claro_claco_form_get_field_by_name_excluding_id',
          {clacoForm: this.props.resourceId, name: this.state.field.name, id: this.props.field.id}
        ),
        {
          method: 'GET' ,
          credentials: 'include'
        }
      )
      .then(response => response.json())
      .then(results => {
        if (JSON.parse(results) === null) {
          this.registerField()
          this.setState({isFetching: false})
        } else {
          this.setState({
            hasError: true,
            nameError: trans('form_not_unique_error', {}, 'clacoform'),
            isFetching: false
          })
        }
      })
    }
  }

  checkLocked() {
    if (this.state.field.required && this.state.field.locked && !this.state.field.lockedEditionOnly) {
      this.setState({field: Object.assign({}, this.state.field, {required: false})})
    }
  }

  updateFieldProps(property, value) {
    this.setState(
      {field: Object.assign({}, this.state.field, {[property]: value})},
      this.checkLocked
    )
  }

  updateFieldDetailsProps(property, value) {
    const details = Object.assign({}, this.state.field.details, {[property]: value})
    this.setState({field: Object.assign({}, this.state.field, {details: details})})
  }

  addNewChoice() {
    const choices = cloneDeep(this.state.choices)
    choices.push({
      index: this.state.choiceIndex,
      value: '',
      new: true,
      category: null,
      error: ''
    })
    this.setState({choices: choices, choiceIndex: this.state.choiceIndex + 1})
  }

  updateChoice(index, property, value) {
    const choices = cloneDeep(this.state.choices)
    const idx = choices.findIndex(c => c.index === index)

    if (idx >= 0) {
      choices[idx] = Object.assign({}, choices[idx], {[property]: value})
      this.setState({choices: choices})
    }
  }

  deleteChoice(index) {
    const choices = cloneDeep(this.state.choices)
    const idx = choices.findIndex(c => c.index === index)

    if (idx >= 0) {
      choices.splice(idx, 1)
      this.setState({choices: choices})
    }
  }

  addChoiceChild(parentIndex) {
    const choicesChildren = cloneDeep(this.state.choicesChildren)

    if (!choicesChildren[parentIndex]) {
      choicesChildren[parentIndex] = []
    }
    choicesChildren[parentIndex].push({
      index: this.state.choiceIndex,
      value: '',
      new: true,
      category: null,
      error: ''
    })
    this.setState({choicesChildren: choicesChildren, choiceIndex: this.state.choiceIndex + 1})
  }

  updateChoiceChild(parentIndex, index, property, value) {
    const choicesChildren = cloneDeep(this.state.choicesChildren)

    if (choicesChildren[parentIndex]) {
      const idx = choicesChildren[parentIndex].findIndex(c => c.index === index)

      if (idx >= 0) {
        choicesChildren[parentIndex][idx] = Object.assign({}, choicesChildren[parentIndex][idx], {[property]: value})
        this.setState({choicesChildren: choicesChildren})
      }
    }
  }

  deleteChoiceChild(parentIndex, index) {
    const choicesChildren = cloneDeep(this.state.choicesChildren)

    if (choicesChildren[parentIndex]) {
      const idx = choicesChildren[parentIndex].findIndex(c => c.index === index)

      if (idx >= 0) {
        choicesChildren[parentIndex].splice(idx, 1)
        this.setState({choicesChildren: choicesChildren})
      }
    }
  }

  addChoicesChildrenFromField(fieldId, parentIndex) {
    const field = this.props.fields.find(f => f.id === fieldId)

    if (field && field.fieldFacet && field.fieldFacet.field_facet_choices.length > 0) {
      let choiceIndex = this.state.choiceIndex
      const choicesChildren = cloneDeep(this.state.choicesChildren)

      field.fieldFacet.field_facet_choices.filter(c => !c.parent).forEach(c => {
        if (!choicesChildren[parentIndex]) {
          choicesChildren[parentIndex] = []
        }
        choicesChildren[parentIndex].push({
          index: choiceIndex,
          value: c.label,
          new: true,
          category: null,
          error: ''
        })
        ++choiceIndex
      })
      this.setState({choicesChildren: choicesChildren, choiceIndex: choiceIndex})
    }
  }

  registerField() {
    if (!this.state['hasError']) {
      this.props.confirmAction(this.state)
      this.props.fadeModal()
    }
  }

  validateField() {
    const validation = {
      hasError: false,
      nameError: null
    }

    if (!this.state.field.name) {
      validation['nameError'] = trans('form_not_blank_error', {}, 'clacoform')
      validation['hasError'] = true
    }
    if (getFieldType(this.state.field.type).hasChoice) {
      validation['hasError'] = !this.validateChoices() || validation['hasError']
    }
    this.setState(validation, this.checkFieldName)
  }

  validateChoices() {
    let valid = true
    const choices = cloneDeep(this.state.choices)
    const choicesChildren = cloneDeep(this.state.choicesChildren)
    choices.forEach(c => c.error = '')
    choices.forEach(c => {
      if (!c.value) {
        c.error = trans('form_not_blank_error', {}, 'clacoform')
        valid = false
      } else {
        choices.forEach(nc => {
          if ((nc.index !== c.index) && (nc.value === c.value)) {
            c.error = trans('form_not_unique_error', {}, 'clacoform')
            nc.error = trans('form_not_unique_error', {}, 'clacoform')
            valid = false
          }
        })
      }
    })

    if (getFieldType(this.state.field.type).hasCascade && choicesChildren) {
      for (let key in choicesChildren) {
        choicesChildren[key].forEach(c => c.error = '')
        choicesChildren[key].forEach(c => {
          if (!c.value) {
            c.error = trans('form_not_blank_error', {}, 'clacoform')
            valid = false
          } else {
            choicesChildren[key].forEach(nc => {
              if ((nc.index !== c.index) && (nc.value === c.value)) {
                c.error = trans('form_not_unique_error', {}, 'clacoform')
                nc.error = trans('form_not_unique_error', {}, 'clacoform')
                valid = false
              }
            })
          }
        })
      }
    }
    this.setState({choices: choices, choicesChildren: choicesChildren})

    return valid
  }

  render() {
    return (
      <BaseModal {...this.props}>
        <Modal.Body>
          <div className={classes('form-group form-group-align row', {'has-error': this.state.nameError})}>
            <label className="control-label col-md-3">
              {t('name')}
            </label>
            <div className="col-md-9">
              <input
                type="text"
                className="form-control"
                value={this.state.field.name}
                onChange={e => this.updateFieldProps('name', e.target.value)}
              />
              {this.state.nameError &&
                <div className="help-block field-error">
                  {this.state.nameError}
                </div>
              }
            </div>
          </div>
          <div className="form-group form-group-align row">
            <label className="control-label col-md-3">
              {t('type')}
            </label>
            <div className="col-md-9">
              <select
                className="form-control"
                name="field-type"
                defaultValue={this.state.field.type}
                onChange={e => this.updateFieldProps('type', parseInt(e.target.value))}
              >
                {constants.FIELD_TYPES.map(ft =>
                  <option
                    key={`field-type-${ft.value}`}
                    value={ft.value}
                  >
                    {ft.label}
                  </option>
                )}
              </select>
            </div>
          </div>
          <hr/>
          {getFieldType(parseInt(this.state.field.type)).name === 'file' &&
            <div>
              <SelectGroup
                controlId="file-field-types"
                label={trans('allowed_file_types', {}, 'clacoform')}
                options={constants.FILE_TYPES}
                selectedValue={this.state.field.details.file_types}
                multiple={true}
                onChange={value => this.updateFieldDetailsProps('file_types', value)}
              />
              <NumberGroup
                controlId="file-field-nb-max"
                label={trans('max_number_of_files', {}, 'clacoform')}
                value={this.state.field.details.nb_files_max}
                min={0}
                onChange={value => this.updateFieldDetailsProps('nb_files_max', value)}
              />
              <hr/>
            </div>
          }
          {this.state.choicesLoaded && getFieldType(parseInt(this.state.field.type)).hasChoice &&
            <div>
              <div className="form-group row">
                <label className="control-label col-md-3">
                  {trans('choices', {}, 'clacoform')}
                </label>
                <div className="col-md-9">
                  {this.state.choices.map(choice =>
                    <ChoiceField
                      key={`choice-${choice.index}-${choice.new ? 'new' : 'old'}`}
                      fieldId={this.state.field.id}
                      choice={choice}
                      choicesChildren={this.state.choicesChildren}
                      hasCascade={getFieldType(this.state.field.type).hasCascade && this.props.cascadeLevelMax > 0}
                      cascadeLevel={0}
                      updateChoice={(index, property, value) => this.updateChoice(index, property, value)}
                      deleteChoice={(index) => this.deleteChoice(index)}
                      addChoiceChild={(parentIndex) => this.addChoiceChild(parentIndex)}
                      updateChoiceChild={(parentIndex, index, property, value) => this.updateChoiceChild(parentIndex, index, property, value)}
                      deleteChoiceChild={(parentIndex, index) => this.deleteChoiceChild(parentIndex, index)}
                      addChoicesChildrenFromField={(fieldId, parentIndex) => this.addChoicesChildrenFromField(fieldId, parentIndex)}
                    />
                  )}
                  <br/>
                  <button
                    className="btn btn-primary btn-sm"
                    onClick={() => this.addNewChoice()}
                  >
                    <span className="fa fa-w fa-plus-circle"></span>
                    {trans('add_choice', {}, 'clacoform')}
                  </button>
                </div>
              </div>
              <hr/>
            </div>
          }
          <div className="clacoform-field-group">
            <CheckGroup
              checkId="field-required"
              checked={this.state.field.required}
              disabled={this.state.field.locked && !this.state.field.lockedEditionOnly}
              label={trans('mandatory', {}, 'clacoform')}
              onChange={checked => this.updateFieldProps('required', checked)}
            />
            <span
              className="fa fa-w fa-info-circle field-tooltip"
              data-toggle="tooltip"
              data-placement="top"
              title={trans('mandatory_locked_conflict', {}, 'clacoform')}
            >
            </span>
          </div>
          <CheckGroup
            checkId="field-is-metadata"
            checked={this.state.field.isMetadata}
            label={trans('metadata', {}, 'clacoform')}
            onChange={checked => this.updateFieldProps('isMetadata', checked)}
          />
          <CheckGroup
            checkId="field-hidden"
            checked={this.state.field.hidden}
            label={trans('hide_field', {}, 'clacoform')}
            onChange={checked => this.updateFieldProps('hidden', checked)}
          />
          <hr/>
          <CheckGroup
            checkId="field-locked"
            checked={this.state.field.locked}
            label={t('locked')}
            onChange={checked => this.updateFieldProps('locked', checked)}
          />
          {this.state.field.locked &&
            <CheckGroup
              checkId="field-locked-edition-only"
              checked={this.state.field.lockedEditionOnly}
              label={trans('edition_only', {}, 'clacoform')}
              onChange={checked => this.updateFieldProps('lockedEditionOnly', checked)}
            />
          }
        </Modal.Body>
        <Modal.Footer>
          <button className="btn btn-default" onClick={this.props.fadeModal}>
            {t('cancel')}
          </button>
          <button className="btn btn-primary" onClick={() => this.validateField()}>
            {this.state.isFetching ?
              <span className="fa fa-fw fa-circle-o-notch fa-spin"></span> :
              <span>{t('ok')}</span>
            }
          </button>
        </Modal.Footer>
      </BaseModal>
    )
  }
}

FieldFormModal.propTypes = {
  resourceId:T.number.isRequired,
  field: T.shape({
    id: T.number,
    name: T.string.isRequired,
    type: T.number.isRequired,
    required: T.boolean,
    isMetadata: T.boolean,
    locked: T.boolean,
    lockedEditionOnly: T.boolean,
    hidden: T.boolean,
    details: T.oneOfType([T.object, T.array]),
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
  }).isRequired,
  cascadeLevelMax: T.number.isRequired,
  fields: T.arrayOf(T.shape({
    id: T.number.isRequired,
    name: T.string.isRequired,
    fieldFacet: T.shape({
      field_facet_choices: T.arrayOf(T.shape({
        id: T.number.isRequired,
        label: T.string.isRequired,
        parent: T.shape({
          id: T.number.isRequired,
          label: T.string.isRequired
        })
      }))
    }),
    details: T.oneOfType([T.object, T.array])
  })),
  confirmAction: T.func.isRequired,
  fadeModal: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    cascadeLevelMax: state.cascadeLevelMax,
    fields: state.fields
  }
}

function mapDispatchToProps() {
  return {}
}

const ConnectedFieldFormModal = connect(mapStateToProps, mapDispatchToProps)(FieldFormModal)

export {ConnectedFieldFormModal as FieldFormModal}
