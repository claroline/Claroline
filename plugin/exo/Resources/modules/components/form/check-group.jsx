import React, {PropTypes as T} from 'react'

export const CheckGroup = ({checkId, label, checked, help, onChange}) =>
  <div className="form-group check-group">
    <div className="checkbox">
      <input
        id={checkId}
        type="checkbox"
        checked={checked}
        aria-describedby={`help-${checkId}`}
        onChange={e => onChange(e.target.checked)}
      />
    </div>
    <label className="control-label" htmlFor={checkId}>{label}</label>
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
