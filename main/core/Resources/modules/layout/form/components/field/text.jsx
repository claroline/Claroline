import React from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

const Text = props => props.long ?
  <textarea
    id={props.id}
    className="form-control"
    value={props.value || ''}
    disabled={props.disabled}
    onChange={(e) => props.onChange(e.target.value)}
    rows={props.minRows}
  />
  :
  <input
    id={props.id}
    type="text"
    className="form-control"
    value={props.value || ''}
    disabled={props.disabled}
    onChange={(e) => props.onChange(e.target.value)}
  />

implementPropTypes(Text, FormFieldTypes, {
  value: T.string,
  long: T.bool,
  minRows: T.number,
  minLength: T.number, // todo implement
  maxLength: T.number // todo implement
}, {
  value: '',
  long: false,
  minRows: 2
})

export {
  Text
}
