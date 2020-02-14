import React, {Fragment} from 'react'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceInput} from '#/main/core/data/types/resource/components/input'
import {NumberInput} from '#/main/app/data/types/number/components/input'

// todo : manages errors

const ResourceCompletedAboveInput = (props) =>
  <Fragment>
    <FormGroup
      id={`${props.id}-resource`}
      label={trans('resource')}
    >
      <ResourceInput
        id={`${props.id}-resource`}
        disabled={props.disabled}
        onChange={(value) => props.onChange({resource: value})}
        value={get(props.value, 'resource')}
        size={props.size}
      />
    </FormGroup>

    <FormGroup
      id={`${props.id}-progression`}
      className="form-last"
      label={trans('progression')}
    >
      <NumberInput
        id={`${props.id}-progression`}
        disabled={props.disabled}
        onChange={(value) => props.onChange({value: value})}
        min={0}
        max={100}
        value={get(props.value, 'value')}
        size={props.size}
        unit="%"
      />
    </FormGroup>
  </Fragment>

implementPropTypes(ResourceCompletedAboveInput, FormFieldTypes, {
  // more precise value type
  value: T.shape({
    resource: T.shape(
      ResourceNodeTypes.propTypes
    ),
    value: T.number
  })
}, {
  value: {
    resource: null,
    value: null
  }
})

export {
  ResourceCompletedAboveInput
}
