import React from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormField as FormFieldType} from '#/main/core/layout/form/prop-types'
import {Select} from '#/main/core/layout/form/components/field/select.jsx'

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

const Cascade = props =>
  <fieldset className="cascade-select">
    {props.choices && props.choices.length > 0 &&
      <Select
        id="cascade-select-lvl-0"
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
      />
    }
    {props.value.map((v, index) => hasChildren(props, index) ?
      <Select
        id={`cascade-select-lvl-${index + 1}`}
        key={`cascade-select-level-${index + 1}`}
        choices={generateChoices(props, index)}
        value={props.value[index + 1] || ''}
        disabled={props.disabled}
        onChange={(value) => {
          const newValue = updateValue(props, index + 1, value)
          props.onChange(newValue)
        }}
      /> :
      ''
    )}
  </fieldset>

implementPropTypes(Cascade, FormFieldType, {
  choices: T.array.isRequired,
  value: T.array
}, {
  value: []
})

export {
  Cascade
}
