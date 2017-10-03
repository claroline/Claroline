import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {trans} from '#/main/core/translation'
import {ChoiceField} from './choice-field.jsx'
import {getFieldType} from '../../../utils'
import {SelectInput} from '#/main/core/layout/form/components/field/select-input.jsx'

class ChoicesChildren  extends Component {
  constructor(props) {
    super(props)
    this.state = {
      showFieldsWithChoices: false,
      selectedField: 0
    }
  }

  updateStateProps(property, value) {
    this.setState({[property]: value})
  }

  addChoicesFromSelectedField() {
    this.props.addChoicesFromField(this.state.selectedField, this.props.parent.index)
    this.setState({
      showFieldsWithChoices: false,
      selectedField: 0
    })
  }

  render() {
    return (
      <div className="choices-children-box row">
        <div className="col-md-1"></div>
        <div className="col-md-11">
          {this.props.choicesChildren[this.props.parent.index] && this.props.choicesChildren[this.props.parent.index].map(c =>
            <ChoiceField
              key={`choice-${this.props.parent.index}-${c.index}-${c.new ? 'new' : 'old'}`}
              fieldId={this.props.fieldId}
              choice={c}
              choicesChildren={this.props.choicesChildren}
              hasCascade={this.props.cascadeLevelMax > this.props.cascadeLevel}
              cascadeLevel={this.props.cascadeLevel}
              updateChoice={(index, property, value) => this.props.updateChoice(this.props.parent.index, index, property, value)}
              deleteChoice={(index) => this.props.deleteChoice(this.props.parent.index, index)}
              addChoiceChild={this.props.addChoice}
              updateChoiceChild={this.props.updateChoice}
              deleteChoiceChild={this.props.deleteChoice}
              addChoicesChildrenFromField={this.props.addChoicesFromField}
            />
          )}
          {this.state.showFieldsWithChoices ?
            <SelectInput
              selectMode={true}
              options={this.props.fields
                .filter(f =>
                  f.id !== this.props.fieldId &&
                  getFieldType(f.type).hasCascade &&
                  f.fieldFacet &&
                  f.fieldFacet.field_facet_choices.length > 0
                )
                .map(f => {
                  return {value: f.id, label: f.name}
                })
              }
              primaryLabel="<span class='fa fa-w fa-plus-circle'></span>"
              secondaryLabel="<span class='fa fa-w fa-times-circle'></span>"
              disablePrimary={!this.state.selectedField}
              typeAhead={false}
              value={this.state.selectedField}
              onChange={value => this.updateStateProps('selectedField', parseInt(value))}
              onPrimary={() => this.addChoicesFromSelectedField()}
              onSecondary={() => {
                this.updateStateProps('showFieldsWithChoices', !this.state.showFieldsWithChoices)
                this.updateStateProps('selectedField', 0)
              }}
            /> :
            <div className="input-group choices-management-buttons">
              <button
                className="btn btn-default btn-sm choices-management-btn"
                onClick={() => this.props.addChoice(this.props.parent.index)}
              >
                <span className="fa fa-w fa-plus-circle"></span>
                &nbsp;
                {trans('add_choice', {}, 'clacoform')}
              </button>
              <button
                className="btn btn-default btn-sm choices-management-btn"
                onClick={() => this.updateStateProps('showFieldsWithChoices', !this.state.showFieldsWithChoices)}
              >
                <span className="fa fa-w fa-bars"></span>
                &nbsp;
                {trans('copy_a_list', {}, 'clacoform')}
              </button>
            </div>
          }
        </div>
      </div>
    )
  }
}

ChoicesChildren.propTypes = {
  fieldId: T.number.isRequired,
  parent: T.shape({
    index: T.number.isRequired,
    value: T.string,
    new: T.bool.isRequired,
    category: T.number,
    error: T.string
  }).isRequired,
  choicesChildren: T.object,
  cascadeLevel: T.number.isRequired,
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
    })
  })),
  addChoice: T.func,
  updateChoice: T.func,
  deleteChoice: T.func,
  addChoicesFromField: T.func
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

const ConnectedChoicesChildren = connect(mapStateToProps, mapDispatchToProps)(ChoicesChildren)

export {ConnectedChoicesChildren as ChoicesChildren}
