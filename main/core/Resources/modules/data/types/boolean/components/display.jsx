import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

const BooleanDisplay = (props) =>
  <div className={classes('boolean-display', {
    true: props.data,
    false: !props.data
  })}>
    {props.icon &&
      <span className={classes('icon-with-text-right', props.icon)} />
    }

    {!props.icon &&
      <span className={classes('fa fa-fw', {
        'fa-check': props.data,
        'fa-times': !props.data
      })} />
    }

    {props.data && props.labelChecked ? props.labelChecked : props.label}
  </div>

BooleanDisplay.propTypes = {
  data: T.bool.isRequired,
  icon: T.string,
  label: T.string.isRequired,
  labelChecked: T.string
}

export {
  BooleanDisplay
}
