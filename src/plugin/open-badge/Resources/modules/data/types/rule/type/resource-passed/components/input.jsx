import React, {Fragment} from 'react'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group'
import {ChoiceInput} from '#/main/app/data/types/choice/components/input'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceInput} from '#/main/core/data/types/resource/components/input'
import {constants} from '#/main/core/resource/constants'

// todo : manages errors

const ResourcePassedInput = (props) =>
  <Fragment>
    <FormGroup
      id={props.id}
      label={trans('resource')}
    >
      <ResourceInput
        {...props}
        onChange={(value) => props.onChange({resource: value})}
        value={get(props.value, 'resource')}
      />
    </FormGroup>

    <FormGroup
      id={`${props.id}-status`}
      className="form-last"
      label={trans('status')}
    >
      <ChoiceInput
        id={`${props.id}-status`}
        disabled={props.disabled}
        onChange={(value) => props.onChange({value: value})}
        value={get(props.value, 'value')}
        size={props.size}
        choices={constants.EVALUATION_STATUSES}
      />
    </FormGroup>
  </Fragment>

implementPropTypes(ResourcePassedInput, DataInputTypes, {
  // more precise value type
  value: T.shape({
    resource: T.shape(
      ResourceNodeTypes.propTypes
    ),
    value: T.string
  })
}, {
  value: {
    resource: null,
    value: null
  }
})

export {
  ResourcePassedInput
}
