import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'
import {Radios} from '#/main/core/layout/form/components/field/radios.jsx'

/**
 * @todo : radios should switch to vertical on xs (maybe sm) screen (MUST be done in less).
 *
 * @param props
 * @constructor
 */
const RadiosGroup = props =>
  <FormGroup {...props}>
    <Radios
      id={props.id}
      inline={props.inline}
      options={props.options}
      value={props.value}
      disabled={props.disabled}
      onChange={props.onChange}
    />
  </FormGroup>

implementPropTypes(RadiosGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.oneOfType([T.string, T.number]),

  // custom props
  options: T.array.isRequired,
  inline: T.bool
}, {
  value: [],
  inline: true
})

export {
  RadiosGroup
}
