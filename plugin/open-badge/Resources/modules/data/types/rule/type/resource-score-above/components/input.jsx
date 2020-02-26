import React, {Fragment} from 'react'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceInput} from '#/main/core/data/types/resource/components/input'
import {NumberInput} from '#/main/app/data/types/number/components/input'

// todo : manages errors

const ResourceScoreAboveInput = (props) =>
  <Fragment>
    <FormGroup
      id={`${props.id}-resource`}
      label={trans('resource')}
    >
      <ResourceInput
        id={`${props.id}-resource`}
        disabled={props.disabled}
        onChange={(resource) => props.onChange({resource: resource})}
        value={get(props.value, 'resource')}
        size={props.size}
      />
    </FormGroup>

    <FormGroup
      id={`${props.id}-score`}
      className="form-last"
      label={trans('score')}
    >
      <NumberInput
        id={`${props.id}-score`}
        disabled={props.disabled}
        onChange={(value) => props.onChange({value: value})}
        min={0}
        value={get(props.value, 'value')}
        size={props.size}
      />
    </FormGroup>
  </Fragment>

implementPropTypes(ResourceScoreAboveInput, DataInputTypes, {
  // more precise value type
  value: T.shape({
    resource: T.shape(
      ResourceNodeTypes.propTypes
    ),
    value: T.number
  })
}, {
  value: {resource: null, value: null}
})

export {
  ResourceScoreAboveInput
}
