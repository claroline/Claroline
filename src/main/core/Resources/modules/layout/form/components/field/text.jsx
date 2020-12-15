import React from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'

const Text = props => {
  if (props.long) {
    return (
      <textarea
        id={props.id}
        key={props.id}
        className={classes('form-control', {[`input-${props.size}`]: !!props.size})}
        value={props.value || ''}
        disabled={props.disabled}
        onChange={(e) => props.onChange(e.target.value)}
        rows={props.minRows}
      />
    )
  }

  return (
    <input
      id={props.id}
      key={props.id}
      type="text"
      className={classes('form-control', {[`input-${props.size}`]: !!props.size})}
      value={props.value || ''}
      disabled={props.disabled}
      onChange={(e) => props.onChange(e.target.value)}
    />
  )
}

implementPropTypes(Text, DataInputTypes, {
  value: T.string,
  long: T.bool,
  minRows: T.number,
  minLength: T.number,
  maxLength: T.number
}, {
  value: '',
  long: false,
  minRows: 4
})

export {
  Text
}
