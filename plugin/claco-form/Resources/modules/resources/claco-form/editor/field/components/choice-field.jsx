import React, {Component} from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import {trans} from '#/main/core/translation'
import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button.jsx'
import {ChoicesChildren} from './choices-children.jsx'

class ChoiceField  extends Component {
  constructor(props) {
    super(props)
    this.state = {
      categoryEnabled: props.choice.category > 0,
      childrenEnabled: false
    }
  }

  updateProps(property, value) {
    this.setState({[property]: value})
  }

  updateChoiceProps(property, value) {
    this.props.updateChoice(this.props.choice.index, property, value)
  }

  switchCategorySelection() {
    const categoryEnabled = !this.state.categoryEnabled
    this.updateProps('categoryEnabled', categoryEnabled)

    if (!categoryEnabled) {
      this.updateChoiceProps('category', null)
    }
  }

  switchChildrenManagement() {
    this.updateProps('childrenEnabled', !this.state.childrenEnabled)
  }

  render() {
    return (
      <div className={classes('clacoform-field-choice', {'has-error': this.props.choice.error})}>
        <div className="input-group">
          <input
            type="text"
            className="form-control"
            value={this.props.choice.value}
            onChange={e => this.updateChoiceProps('value', e.target.value)}
          />
          <span className="input-group-btn">
            {this.props.hasCascade &&
              <TooltipButton
                id={`tooltip-children-button-${this.props.choice.index}`}
                className={classes(
                  'btn btn-default',
                  {
                    'text-muted': !this.props.choicesChildren[this.props.choice.index] ||
                      this.props.choicesChildren[this.props.choice.index].length === 0
                  }
                )}
                title={trans('sub_list_management', {}, 'clacoform')}
                onClick={() => this.switchChildrenManagement()}
              >
                <span className="fa fa-w fa-list-ul"></span>
              </TooltipButton>
            }
            <TooltipButton
              id={`tooltip-category-button-${this.props.choice.index}`}
              className={`btn btn-${this.state.categoryEnabled ? 'warning' : 'default'}`}
              title={this.state.categoryEnabled ?
                trans('remove_category', {}, 'clacoform') :
                trans('associate_category', {}, 'clacoform')
              }
              onClick={() => this.switchCategorySelection()}
            >
              <span className="fa fa-w fa-inbox"></span>
            </TooltipButton>
            <button
              type="button"
              className="btn btn-danger"
              onClick={() => this.props.deleteChoice(this.props.choice.index)}
            >
              <span className="fa fa-w fa-trash"></span>
            </button>
          </span>
        </div>
        {this.props.choice.error &&
          <div className="help-block field-error">
            {this.props.choice.error}
          </div>
        }
        {this.state.categoryEnabled &&
          <select
            className="form-control"
            name="choice-category"
            defaultValue={this.props.choice.category}
            onChange={e => this.updateChoiceProps('category', parseInt(e.target.value))}
          >
            <option value=""></option>
            {this.props.categories.map(category =>
              <option
                key={`category-${category.id}-${this.props.choice.index}`}
                value={category.id}
              >
                {category.name}
              </option>
            )}
          </select>
        }
        {this.props.hasCascade && this.state.childrenEnabled &&
          <ChoicesChildren
            fieldId={this.props.fieldId}
            parent={this.props.choice}
            choicesChildren={this.props.choicesChildren}
            cascadeLevel={this.props.cascadeLevel + 1}
            addChoice={this.props.addChoiceChild}
            updateChoice={this.props.updateChoiceChild}
            deleteChoice={this.props.deleteChoiceChild}
            addChoicesFromField={this.props.addChoicesChildrenFromField}
          />
        }
      </div>
    )
  }
}

ChoiceField.propTypes = {
  fieldId: T.number.isRequired,
  choice: T.shape({
    index: T.number.isRequired,
    value: T.string,
    new: T.bool.isRequired,
    category: T.number,
    error: T.string
  }).isRequired,
  choicesChildren: T.object,
  categories: T.arrayOf(T.shape({
    id: T.number.isRequired,
    name: T.string.isRequired
  })).isRequired,
  hasCascade: T.bool.isRequired,
  cascadeLevel: T.number.isRequired,
  updateChoice: T.func.isRequired,
  deleteChoice: T.func.isRequired,
  addChoiceChild: T.func,
  updateChoiceChild: T.func,
  deleteChoiceChild: T.func,
  addChoicesChildrenFromField: T.func
}

function mapStateToProps(state) {
  return {
    categories: state.categories
  }
}

function mapDispatchToProps() {
  return {}
}

const ConnectedChoiceField = connect(mapStateToProps, mapDispatchToProps)(ChoiceField)

export {ConnectedChoiceField as ChoiceField}
