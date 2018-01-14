import React from 'react'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'

import {FormGroupWithField as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {Checkbox} from '#/main/core/layout/form/components/field/checkbox.jsx'
import {HelpBlock} from '#/main/core/layout/form/components/help-block.jsx'

const CheckGroup = props =>
  <div className="form-group check-group">
    <Checkbox
      id={props.id}
      checked={props.value}
      disabled={props.disabled}
      label={props.label}
      labelChecked={props.labelChecked}
      onChange={props.onChange}
    />

    {props.help &&
      <HelpBlock help={props.help} />
    }
  </div>

implementPropTypes(CheckGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.bool,
  // custom props
  labelChecked: T.string
}, {
  value: false
})

export {
  CheckGroup
}
