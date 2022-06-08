import React, {Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import has from 'lodash/has'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {Schema as SchemaTypes} from '#/main/transfer/tools/transfer/prop-types'

const Fields = props =>
  <Fragment>
    {props.properties.map((prop, index) =>
      <Field key={index} {...prop} />
    )}
  </Fragment>

const Field = props => {
  if (has(props, 'oneOf')) {
    return (
      <div className={classes('transfer-schema-one-of', {
        required: props.required
      })}>
        <div className="schema-field-meta">
          {trans('one_of_field_list', {}, 'transfer')}

          {props.required &&
            <span className="label label-primary">{trans('required')}</span>
          }
        </div>

        <div className="transfer-schema-fields">
          <div className="transfer-schema-fields-list">
            {props.oneOf.map((oneOf, index) => oneOf.properties.map(property =>
              <Field key={index} {...property} required={false} />
            ))}
          </div>

          <div className="transfer-schema-or">
            {trans('or')}
          </div>
        </div>
      </div>
    )
  }

  let types = []
  if (props.type) {
    types = !Array.isArray(props.type) ? [props.type] : props.type
    types = types.filter(type => 'null' !== type)
  }

  return (
    <div className={classes('transfer-schema-field', {
      required: props.required
    })}>
      <div className="schema-field-meta">
        <strong>
          {props.name}
          <small className="text-muted">
            ({types.map(type => {
              if (props.isArray) {
                return trans('array_of', {type: trans(type, {}, 'data')}, 'data')
              }

              return trans(type, {}, 'data')
            }).join(' / ')})
          </small>
        </strong>

        {props.required &&
          <span className="label label-primary">{trans('required')}</span>
        }
      </div>

      <p>{props.description}</p>
    </div>
  )
}

class ImportExplanation extends Component {
  constructor(props) {
    super(props)

    this.state = {
      columns: 'all'
    }
  }

  render() {
    if (this.props.schema.properties && 0 !== this.props.schema.properties.length) {
      return (
        <Fragment>
          <div className="transfer-schema-filter">
            {trans('list_filter_by')}
            <Button
              type={CALLBACK_BUTTON}
              className="btn btn-link"
              label={trans('all' === this.state.columns ? 'schema_all_properties' : 'schema_required_properties', {}, 'transfer')}
              callback={() => this.setState({columns: 'all' === this.state.columns ? 'required' : 'all'})}
              primary={true}
            />
          </div>

          <Fields
            properties={this.props.schema.properties
              .filter(prop => 'all' === this.state.columns || prop.required)
            }
          />
        </Fragment>
      )
    }

    return null
  }
}

ImportExplanation.propTypes = {
  schema: T.shape(SchemaTypes.propTypes)
}

export {
  ImportExplanation
}