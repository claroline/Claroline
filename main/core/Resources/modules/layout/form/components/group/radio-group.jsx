import React from 'react'
import {PropTypes as T} from 'prop-types'

import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'
import {Radios} from '#/main/core/layout/form/components/field/radios.jsx'

/**
 * @todo : radios should switch to vertical on xs (maybe sm) screen (MUST be done in less).
 *
 * @param props
 * @constructor
 */
const RadioGroup = props =>
  <FormGroup
    {...props}
  >
    <Radios
      groupName={props.controlId}
      inline={true}
      options={props.options}
      checkedValue={props.checkedValue}
      onChange={props.onChange}
    />
  </FormGroup>

RadioGroup.propTypes = {
  controlId: T.string.isRequired,
  options: T.array.isRequired,
  checkedValue: T.string.isRequired,
  onChange: T.func.isRequired
}

export {
  RadioGroup
}
