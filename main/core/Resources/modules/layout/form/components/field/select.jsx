import React from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

// TODO : implement disabledChoices

const getSelectedValues = (e) => {
  const values = []

  for (let i = 0; i < e.target.options.length; i++) {
    if (e.target.options[i].selected) {
      values.push(!isNaN(e.target.options[i].value) ? parseFloat(e.target.options[i].value) : e.target.options[i].value)
    }
  }

  return values
}

const Select = props =>
  <select
    id={props.id}
    className={classes('form-control', {[`input-${props.size}`]: !!props.size})}
    value={props.value || ''}
    disabled={props.disabled}
    onChange={e => props.multiple ?
      props.onChange(getSelectedValues(e)) :
      props.onChange(!isNaN(e.target.value) ? parseFloat(e.target.value) : e.target.value)
    }
    multiple={props.multiple}
  >
    {!props.multiple && !props.noEmpty &&
      <option value=""/>
    }

    {Object.keys(props.choices).map(option =>
      <option
        key={option}
        value={option}
        disabled={-1 !== props.disabledChoices.indexOf(option)}
      >
        {props.choices[option]}
      </option>
    )}
  </select>

implementPropTypes(Select, FormFieldTypes, {
  choices: T.object.isRequired,
  disabledChoices: T.arrayOf(T.string),
  value: T.oneOfType([T.string, T.number, T.array]),
  multiple: T.bool,
  noEmpty: T.bool
}, {
  value: '',
  disabledChoices: [],
  multiple: false,
  noEmpty: false
})

export {
  Select
}
