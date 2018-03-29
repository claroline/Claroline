import React from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

const Email = props =>
  <input
    id={props.id}
    type="email"
    autoComplete="email"
    className="form-control"
    value={props.value || ''}
    disabled={props.disabled}
    onChange={(e) => props.onChange(e.target.value)}
  />

implementPropTypes(Email, FormFieldTypes, {
  // more precise value type
  value: T.string
}, {
  value: ''
})

export {
  Email
}
