import React from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataSearch as DataSearchTypes} from '#/main/app/data/types/prop-types'
import {Select} from '#/main/app/input/components/select'
import classes from 'classnames'

const hasChildren = (props, lvl) => {
  const choices = props.choices.slice()
  let child = choices.find(c => props.value && c.value === props.value[0])

  for (let i = 1; i <= lvl; ++i) {
    child = child.children.find(c => c.value === props.value[i])
  }

  return child && child.children && child.children.length > 0
}

const generateChoices = (props, lvl) => {
  const choices = props.choices.slice()
  let child = choices.find(c => props.value && c.value === props.value[0])

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
  const newValue = props.value ? props.value.slice() : []

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

const CascadeFilter = props =>
  <fieldset className={classes('cascade-control d-flex gap-2 flex-direction-row', props.className)}>
    {props.choices && props.choices.length > 0 &&
      <Select
        id={`${props.id}-select-lvl-0`}
        choices={
          props.choices.reduce((acc, choice) => {
            acc[choice.value] = choice.label

            return acc
          }, {})
        }
        value={props.value ? props.value[0] : ''}
        disabled={props.disabled}
        onChange={(value) => {
          const newValue = updateValue(props, 0, value)
          props.onChange(newValue)
        }}
        size={props.size}
      />
    }

    {props.value && props.value.map((v, index) => hasChildren(props, index) ?
      <Select
        key={`select-level-${index + 1}`}
        id={`${props.id}-select-lvl-${index + 1}`}
        choices={generateChoices(props, index)}
        value={props.value[index + 1] || ''}
        disabled={props.disabled}
        onChange={(value) => {
          const newValue = updateValue(props, index + 1, value)
          props.onChange(newValue)
        }}
        size={props.size}
      /> :
      ''
    )}
  </fieldset>

implementPropTypes(CascadeFilter, DataSearchTypes, {
  choices: T.array.isRequired,
  value: T.array
}, {
  value: []
})

export {
  CascadeFilter
}
