import React from 'react'
import classes from 'classnames'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {StringInput} from '#/main/app/data/types/string/components/input'

// todo check uniqueness (maybe do it in the data type)
// todo add username requirements
// todo use app/input instead of string
// todo manage size

const UsernameInput = props =>
  <div className={classes('input-group', this.props.className, {
    [`input-group-${this.props.size}`]: !!this.props.size
  })}>
    <span className="input-group-addon">
      <span className="fa fa-fw fa-user" role="presentation" />
    </span>

    <StringInput
      id={props.id}
      value={props.value}
      disabled={props.disabled}
      onChange={props.onChange}
    />
  </div>

implementPropTypes(UsernameInput, FormFieldTypes, {
  // more precise value type
  value: T.string
}, {
  value: ''
})

export {
  UsernameInput
}