import React, {createElement} from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

const PickerButton = props =>
  <Button
    className="btn btn-outline-primary w-100 mt-2"
    type={MODAL_BUTTON}
    icon="fa fa-fw fa-plus"
    label={trans('add', {}, 'actions')}
    modal={[props.type, {
      url: props.url,
      title: props.title,
      filters: props.filters,
      selectAction: (selected) => ({
        type: CALLBACK_BUTTON,
        label: trans('select', {}, 'actions'),
        callback: () => props.onChange(props.multiple ? selected : selected[0])
      })
    }]}
    size={props.size}
    disabled={props.disabled}
  />

PickerButton.propTypes = {
  type: T.string.isRequired,
  url: T.oneOfType([T.string, T.array]),
  title: T.string,
  filters: T.arrayOf(T.shape({
    // list filter types
  })),
  onChange: T.func.isRequired,
  size: T.string,
  disabled: T.bool,
  multiple: T.bool
}

const EntityInput = props => {
  const actions = props.disabled ? [] : [
    {
      name: 'delete',
      type: CALLBACK_BUTTON,
      icon: 'fa fa-fw fa-trash',
      label: trans('delete', {}, 'actions'),
      dangerous: true,
      callback: () => props.onChange(null)
    }
  ]

  if (props.value) {
    return (
      <>
        {createElement(props.card, {
          data: props.value,
          size: 'xs',
          actions: actions
        })}

        {!props.disabled &&
          <PickerButton
            {...props.picker}
            size={props.size}
            onChange={props.onChange}
            multiple={props.multiple}
          />
        }
      </>
    )
  }

  return (
    <ContentPlaceholder
      id={props.id}
      icon={props.icon}
      title={props.placeholder}
      size={props.size}
    >
      <PickerButton
        {...props.picker}
        size={props.size}
        disabled={props.disabled}
        onChange={props.onChange}
      />
    </ContentPlaceholder>
  )
}

implementPropTypes(EntityInput, DataInputTypes, {
  value: T.object,
  picker: T.shape({
    type: T.string.isRequired,
    url: T.oneOfType([T.string, T.array]),
    title: T.string,
    filters: T.arrayOf(T.shape({
      // list filter types
    }))
  }).isRequired,
  card: T.any,
  icon: T.string,
  placeholder: T.string,
  multiple: T.bool
}, {
  value: null,
  picker: {},
  multiple: false
})

export {
  EntityInput
}
