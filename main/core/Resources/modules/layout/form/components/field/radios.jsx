import React from 'react'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {toKey} from '#/main/core/scaffolding/text'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

const parseValue = (value) => !isNaN(value) ? parseFloat(value) : value

const Radio = props =>
  <div
    className={classes({
      'radio'       : !props.inline,
      'radio-inline': props.inline
    })}
  >
    <label htmlFor={props.id}>
      <input
        type="radio"
        id={props.id}
        value={props.value}
        checked={props.checked}
        disabled={props.disabled}
        onChange={() => props.onChange(parseValue(props.value))}
      />

      {props.label}
    </label>
  </div>

Radio.propTypes = {
  id: T.oneOfType([T.string, T.number]).isRequired,
  label: T.string.isRequired,
  value: T.oneOfType([T.string, T.number]),
  inline: T.bool,
  checked: T.bool,
  disabled: T.bool,
  onChange: T.func.isRequired
}

Radio.defaultProps = {
  checked: false,
  disabled: false
}

/**
 * Renders a list of radio inputs.
 */
const Radios = props => {
  const choiceValues = Object.keys(props.choices)

  if (0 === choiceValues.length) {
    return (
      <em className="text-muted">{trans('no_choice')}</em>
    )
  }

  return (
    <fieldset>
      {choiceValues.map(choiceValue =>
        <Radio
          key={choiceValue}
          id={toKey(choiceValue)}
          label={props.choices[choiceValue]}
          value={choiceValue}
          inline={props.inline}
          checked={parseValue(choiceValue) === props.value}
          disabled={props.disabled || -1 !== props.disabledChoices.indexOf(choiceValue)}
          onChange={props.onChange}
        />
      )}
    </fieldset>
  )
}

implementPropTypes(Radios, FormFieldTypes, {
  value: T.oneOfType([T.string, T.number]),

  // custom props
  choices: T.object.isRequired,
  disabledChoices: T.arrayOf(T.string),
  inline: T.bool
}, {
  value: '',
  disabledChoices: [],
  inline: true
})

export {
  Radios
}
