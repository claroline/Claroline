import React from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {Select} from '#/main/app/input/components/select'
import classes from 'classnames'

const hasChildren = (props, lvl) => {
  const choices = props.choices.slice()
  let child = choices.find(c => c.value === props.value[0])

  for (let i = 1; i <= lvl; ++i) {
    child = child.children.find(c => c.value === props.value[i])
  }

  return child && child.children && child.children.length > 0
}

const generateChoices = (props, lvl) => {
  const choices = props.choices.slice()
  let child = choices.find(c => c.value === props.value[0])

  for (let i = 1; i <= lvl; ++i) {
    child = child.children.find(c => c.value === props.value[i])
  }

  return child.children && child.children.length > 0 ?
    child.children.reduce((acc, choice) => {
      acc[choice.value] = choice.label

      return acc
    }, {}) :
    {}
}

const updateValue =  (props, level, value) => {
  const newValue = props.value.slice()

  if (value) {
    newValue[level] = value

    if (level + 1 < newValue.length) {
      newValue.splice(level + 1)
    }
  } else {
    newValue.splice(level)
  }

  return newValue
}

const CascadeInput = props =>
  <fieldset className={classes('cascade-control', props.className)}>
    {props.choices && props.choices.length > 0 &&
      <Select
        id={`${props.id}-select-lvl-0`}
        choices={
          props.choices.reduce((acc, choice) => {
            acc[choice.value] = choice.label

            return acc
          }, {})
        }
        value={props.value[0] || ''}
        disabled={props.disabled}
        onChange={(value) => {
          const newValue = updateValue(props, 0, value)
          props.onChange(newValue)
        }}
        size={props.size}
      />
    }

    {props.value.map((v, index) => hasChildren(props, index) ?
      <div className="sub-fields mt-2" key={`select-level-${index + 1}`}>
        <Select
          id={`${props.id}-select-lvl-${index + 1}`}
          choices={generateChoices(props, index)}
          value={props.value[index + 1] || ''}
          disabled={props.disabled}
          onChange={(value) => {
            const newValue = updateValue(props, index + 1, value)
            props.onChange(newValue)
          }}
          size={props.size}
        />
      </div> :
      ''
    )}
  </fieldset>

implementPropTypes(CascadeInput, DataInputTypes, {
  choices: T.array.isRequired,
  value: T.array
}, {
  value: []
})

export {
  CascadeInput
}
