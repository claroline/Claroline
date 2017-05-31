import React from 'react'
import {PropTypes as T} from 'prop-types'

export const CheckGroup = ({checkId, label, checked, help, onChange}) =>
  <div className="form-group check-group">
    <div className="checkbox">
      <label htmlFor={checkId}>
        <input
          id={checkId}
          type="checkbox"
          checked={checked}
          aria-describedby={`help-${checkId}`}
          onChange={e => onChange(e.target.checked)}
        />

        {label}
      </label>
    </div>
    {help &&
      <span id={`help-${checkId}`} className="help-block">
        <span className="fa fa-info-circle"></span>
        {help}
      </span>
    }
  </div>

CheckGroup.propTypes = {
  checkId: T.string.isRequired,
  label: T.string.isRequired,
  checked: T.bool.isRequired,
  onChange: T.func.isRequired,
  help: T.string
}
