import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Numeric} from '#/main/core/layout/form/components/field/numeric.jsx'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'

const NumberGroup = props =>
  <FormGroup {...props}>
    <Numeric
      id={props.controlId}
      value={props.value}
      disabled={props.disabled}
      min={props.min}
      max={props.max}
      onChange={props.onChange}
    />
  </FormGroup>

NumberGroup.propTypes = {
  controlId: T.string.isRequired,
  value: T.oneOfType([T.number, T.string]),
  min: T.number,
  max: T.number,
  disabled: T.bool,
  onChange: T.func.isRequired
}

export {
  NumberGroup
}
