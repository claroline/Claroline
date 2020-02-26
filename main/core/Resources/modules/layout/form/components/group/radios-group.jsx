import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {DataGroup as DataGroupTypes, DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {FormGroup} from '#/main/app/content/form/components/group.jsx'
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

implementPropTypes(RadiosGroup, [DataGroupTypes, DataInputTypes], {
  // more precise value type
  value: T.oneOfType([T.string, T.number]),

  // custom props
  choices: T.object.isRequired,
  inline: T.bool
})

export {
  RadiosGroup
}
