import React from 'react'
import classes from 'classnames'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {StringInput} from '#/main/app/data/types/string/components/input'

// todo add username requirements

const UsernameInput = props =>
  <div className={classes('input-group', props.className, {
    [`input-group-${props.size}`]: !!props.size
  })}>
    <span className="input-group-addon">
      <span className="fa fa-fw fa-user" role="presentation" />
    </span>

    <StringInput
      id={props.id}
      value={props.value}
      disabled={props.disabled}
      placeholder={props.placeholder}
      onChange={props.onChange}
    />
  </div>

implementPropTypes(UsernameInput, DataInputTypes, {
  // more precise value type
  value: T.string
}, {
  value: ''
})

export {
  UsernameInput
}
