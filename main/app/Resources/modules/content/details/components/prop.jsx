import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import merge from 'lodash/merge'

import {Await} from '#/main/app/components/await'
import {trans} from '#/main/core/translation'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group'
import {getType} from '#/main/app/data'

import {DataType as DataTypeTypes} from '#/main/app/data/prop-types'

// todo there are big c/c from Form component but I don't know if we can do better

const DataDetailsField = props =>
  <div id={props.name} className={props.className}>
    {(!props.value && false !== props.value) &&
      <span className="data-details-empty">{trans('empty_value')}</span>
    }

    {(props.value || false === props.value) && (props.definition.components.details ?
      React.createElement(props.definition.components.details, merge({}, props.options, {
        id: props.name,
        label: props.label,
        hideLabel: props.hideLabel,
        data: props.value // todo rename into `value` in implementations later
      }))
      :
      props.definition.render ? props.definition.render(props.value, props.options || {}) : props.value
    )}
  </div>

DataDetailsField.propTypes = {
  value: T.any,
  name: T.string.isRequired,
  type: T.string,
  label: T.string.isRequired,
  hideLabel: T.bool,
  options: T.object,
  className: T.string,
  definition: T.shape(
    DataTypeTypes.propTypes
  ).isRequired
}

class DetailsProp extends Component {
  constructor(props) {
    super(props)

    this.state = {definition: null}
  }

  render() {
    return (
      <Await
        for={getType(this.props.type)}
        then={typeDef => this.setState({definition: typeDef})}
      >
        {this.state.definition &&
          <FormGroup
            id={this.props.name}
            label={this.state.definition.meta && this.state.definition.meta.noLabel ? this.props.label : undefined}
            hideLabel={this.props.hideLabel}
            help={this.props.help}
          >
            {this.props.render ?
              this.props.render(this.props.data) :
              <DataDetailsField
                {...this.props}
                definition={this.state.definition}
                value={this.props.calculated ? this.props.calculated(this.props.data) : get(this.props.data, this.props.name)}
              />
            }
          </FormGroup>
        }
      </Await>
    )
  }
}

export {
  DetailsProp
}