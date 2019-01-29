import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import merge from 'lodash/merge'

import {Await} from '#/main/app/components/await'
import {trans} from '#/main/app/intl/translation'
import {FormGroup} from '#/main/app/content/form/components/group'
import {getType} from '#/main/app/data/types'

import {DataType as DataTypeTypes} from '#/main/app/data/types/prop-types'

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

const DetailsProp = props =>
  <Await
    for={getType(props.type)}
    then={definition => (
      <FormGroup
        id={props.name}
        label={definition.meta && !definition.meta.noLabel ? props.label : undefined}
        hideLabel={props.hideLabel}
        help={props.help}
      >
        {props.render ?
          props.render(props.data) :
          <DataDetailsField
            {...props}
            definition={definition}
            value={props.calculated ? props.calculated(props.data) : get(props.data, props.name)}
          />
        }
      </FormGroup>
    )}
  />

export {
  DetailsProp
}