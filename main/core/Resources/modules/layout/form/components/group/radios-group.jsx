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
    <Radios {...props} />
  </FormGroup>

implementPropTypes(RadiosGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.oneOfType([T.string, T.number]),

  // custom props
  choices: T.object.isRequired,
  inline: T.bool
})

export {
  RadiosGroup
}
