import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {ErrorBlock} from './error-block.jsx'

export const FormGroup = props =>
  <div className={classes('form-group', props.className, {
    'form-last': props.last,
    'has-error': props.error && !props.warnOnly,
    'has-warning': props.error && props.warnOnly
  })}>
    <label
      className={classes('control-label', {'sr-only': props.hideLabel})}
      htmlFor={props.controlId}
    >
      {props.label}
    </label>

    {props.children}

    {props.error &&
      <ErrorBlock text={props.error} inGroup={true} warnOnly={props.warnOnly}/>
    }

    {props.help &&
      <span id={`help-${props.controlId}`} className="help-block">
        <span className="fa fa-info-circle" />
        {props.help}
      </span>
    }
  </div>

FormGroup.propTypes = {
  controlId: T.string.isRequired,
  label: T.string.isRequired,
  hideLabel: T.bool,
  className: T.string,
  children: T.element.isRequired,
  warnOnly: T.bool.isRequired,
  help: T.string,
  error: T.string,
  last: T.bool
}

FormGroup.defaultProps = {
  warnOnly: false,
  hideLabel: false,
  last: false
}
