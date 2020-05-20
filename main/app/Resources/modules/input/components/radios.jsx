import React from 'react'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {toKey} from '#/main/core/scaffolding/text'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'

const parseValue = (value) => !isNaN(value) ? parseFloat(value) : value

const Radio = props =>
  <div
    className={classes({
      'radio'       : !props.inline,
      'radio-inline': props.inline,
      'selected': props.checked,
      'disabled': props.disabled
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
    <div id={props.id} className={props.className}>
      {!props.noEmpty &&
        <Radio
          key="empty-value"
          id={`${props.id}-empty`}
          label={props.placeholder || trans('none')}
          value={null}
          inline={props.inline}
          checked={null === props.value}
          onChange={props.onChange}
        />
      }

      {choiceValues.map(choiceValue =>
        <Radio
          key={choiceValue}
          id={`${props.id}-${toKey(choiceValue)}`}
          label={props.choices[choiceValue]}
          value={choiceValue}
          inline={props.inline}
          checked={parseValue(choiceValue) === props.value}
          disabled={props.disabled || -1 !== props.disabledChoices.indexOf(choiceValue)}
          onChange={props.onChange}
        />
      )}
    </div>
  )
}

implementPropTypes(Radios, DataInputTypes, {
  value: T.oneOfType([T.string, T.number]),

  // custom props
  choices: T.object.isRequired,
  disabledChoices: T.arrayOf(T.string),
  inline: T.bool,
  noEmpty: T.bool
}, {
  value: '',
  disabledChoices: [],
  inline: true,
  noEmpty: true
})

export {
  Radios
}
