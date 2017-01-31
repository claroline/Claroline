import React, {PropTypes as T} from 'react'
import classes from 'classnames'
import {ErrorBlock} from './error-block.jsx'

export const FormGroup = ({controlId, label, help, error, children, warnOnly}) =>
  <div className={classes('form-group', {
    'has-error': error && !warnOnly,
    'has-warning': error && warnOnly
  })}>
    <label className="control-label" htmlFor={controlId}>{label}</label>
    {children}
    {error &&
      <ErrorBlock text={error} inGroup={true} warnOnly={warnOnly}/>
    }
    {help &&
      <span id={`help-${controlId}`} className="help-block">
        <span className="fa fa-info-circle"></span>
        {help}
      </span>
    }
  </div>

FormGroup.propTypes = {
  controlId: T.string.isRequired,
  label: T.string.isRequired,
  children: T.element.isRequired,
  warnOnly: T.bool.isRequired,
  help: T.string,
  error: T.string
}

FormGroup.defaultProps = {
  warnOnly: false
}
