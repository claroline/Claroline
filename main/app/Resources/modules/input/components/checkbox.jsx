import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

const Checkbox = props =>
  <div className={classes({
    'checkbox'       : !props.inline,
    'checkbox-inline': props.inline
  }, props.className)}>
    <label>
      <input
        id={props.id}
        type="checkbox"
        checked={props.checked}
        disabled={props.disabled}
        onChange={e => props.onChange(e.target.checked)}
      />

      {props.checked && props.labelChecked ? props.labelChecked : props.label}
    </label>
  </div>

Checkbox.propTypes = {
  id: T.string.isRequired,
  className: T.string,
  label: T.node.isRequired,
  labelChecked: T.node,
  checked: T.bool.isRequired,
  disabled: T.bool,
  inline: T.bool,
  onChange: T.func.isRequired
}

Checkbox.defaultProps = {
  disabled: false,
  inline: false
}

export {
  Checkbox
}
