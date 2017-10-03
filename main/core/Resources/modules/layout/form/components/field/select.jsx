import React from 'react'
import {PropTypes as T} from 'prop-types'

const getSelectedValues = (e) => {
  const values = []

  for (let i = 0; i < e.target.options.length; i++) {
    if (e.target.options[i].selected) {
      values.push(e.target.options[i].value)
    }
  }

  return values
}

const Select = props =>
  <fieldset>
    <select
      className="form-control"
      value={props.selectedValue}
      disabled={props.disabled}
      onChange={e => props.multiple ? props.onChange(getSelectedValues(e)) : props.onChange(e.target.value)}
      multiple={props.multiple}
    >
      {!props.multiple && !props.noEmpty &&
        <option value=""/>
      }
      {props.options.map(option =>
        <option key={option.value} value={option.value}>
          {option.label}
        </option>
      )}
    </select>
  </fieldset>

Select.propTypes = {
  options: T.arrayOf(T.shape({
    value: T.oneOfType([T.string, T.number]).isRequired,
    label: T.string.isRequired
  })).isRequired,
  selectedValue: T.oneOfType([T.string, T.number, T.array]).isRequired,
  disabled: T.bool,
  multiple: T.bool,
  noEmpty: T.bool,
  onChange: T.func.isRequired
}

export {
  Select
}
