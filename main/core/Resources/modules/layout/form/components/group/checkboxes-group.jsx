import React from 'react'
import {PropTypes as T} from 'prop-types'

import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'
import {Checkboxes} from '#/main/core/layout/form/components/field/checkboxes.jsx'

/**
 * @param props
 * @constructor
 */
const CheckboxesGroup = props =>
  <FormGroup {...props}>
    <Checkboxes
      groupName={props.controlId}
      inline={props.inline}
      options={props.options}
      checkedValues={props.checkedValues || []}
      disabled={props.disabled}
      onChange={props.onChange}
    />
  </FormGroup>

CheckboxesGroup.propTypes = {
  controlId: T.string.isRequired,
  options: T.array.isRequired,
  checkedValues: T.array.isRequired,
  inline: T.bool.isRequired,
  disabled: T.bool.isRequired,
  onChange: T.func.isRequired
}

CheckboxesGroup.defaultProps = {
  inline: true,
  disabled: false
}

export {
  CheckboxesGroup
}
