import React, {createElement} from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import isEmpty from 'lodash/isEmpty'
import {DataMicro} from '#/main/app/data/components/micro'

const PickerButton = props =>
  <Button
    className={props.className}
    type={MODAL_BUTTON}
    icon={props.icon}
    label={props.label}
    modal={[props.type, Object.assign({}, props.picker, {
      subtitle: props.help,
      multiple: props.multiple,
      selectAction: (selected) => ({
        type: CALLBACK_BUTTON,
        label: props.label,
        callback: () => {
          if (props.multiple) {
            const newValue = [].concat(props.value || [])
            selected.forEach(object => {
              const index = newValue.findIndex(o => o.id === object.id)

              if (-1 === index) {
                newValue.push(object)
              }
            })

            props.onChange(newValue)
          } else {
            props.onChange(selected[0])
          }
        }
      })
    })]}
    size={props.size}
    disabled={props.disabled}
  />

PickerButton.propTypes = {
  className: T.string,
  type: T.string.isRequired,
  picker: T.shape({
    url: T.oneOfType([T.string, T.array]),
    title: T.string,
    filters: T.arrayOf(T.shape({
      // list filter types
    }))
  }),
  value: T.oneOfType([
    T.object, // multiple = false
    T.arrayOf(T.object) // multiple = true
  ]),
  label: T.string.isRequired,
  onChange: T.func.isRequired,
  size: T.string,
  disabled: T.bool,
  multiple: T.bool
}

const EntityInput = (props) => {
  if (isEmpty(props.value)) {
    return (
      <div className="py-1" role="presentation">
        <PickerButton
          className="btn-add btn btn-link text-start ms-n2 px-2"
          icon="fa fa-plus"
          label={props.add}
          title={props.label}
          picker={props.picker}
          type={props.pickerType}
          size={props.size}
          value={props.value}
          onChange={props.onChange}
          multiple={props.multiple}
          disabled={props.disabled}
          help={props.help}
        />
      </div>
    )
  }

  if (props.multiple) {
    return (
      <ul className="list-group list-group-flush mb-0 border-top border-bottom">
        {props.value.map(object => (
          <li key={object.id} className="list-group-item d-flex align-items-center gap-3 px-0">
            {createElement(props.card, {
              object: object,
              className: 'fw-normal'
            })}

            <Button
              className="btn btn-link ms-auto me-n2"
              size="sm"
              {...{
                name: 'remove',
                type: CALLBACK_BUTTON,
                label: trans('remove', {}, 'actions'),
                disabled: props.disabled,
                callback: () => {
                  const newValue = [].concat(props.value || [])
                  const index = newValue.findIndex(o => o.id === object.id)

                  if (-1 < index) {
                    newValue.splice(index, 1)
                    props.onChange(newValue)
                  }
                }
              }}
            />
          </li>
        ))}

        <li className="list-group-item py-1 px-0">
          <PickerButton
            className="btn-add btn btn-link text-start ms-n2 px-2"
            icon="fa fa-plus"
            label={props.add}
            title={props.label}
            picker={props.picker}
            type={props.pickerType}
            size={props.size}
            value={props.value}
            onChange={props.onChange}
            multiple={props.multiple}
            disabled={props.disabled}
            help={props.help}
          />
        </li>
      </ul>
    )
  }

  return (
    <div className="d-flex align-items-center gap-3 border-top border-bottom" role="presentation" style={{padding: '.75rem 0'}}>
      {createElement(props.card, {
        object: props.value,
        className: 'fw-normal'
      })}

      <Button
        className="btn btn-link ms-auto me-n2"
        size="sm"
        {...{
          type: CALLBACK_BUTTON,
          label: trans('remove', {}, 'actions'),
          disabled: props.disabled,
          callback: () => props.onChange(null)
        }}
      />
    </div>
  )
}

implementPropTypes(EntityInput, DataInputTypes, {
  value: T.oneOfType([
    T.object, // multiple = false
    T.arrayOf(T.object) // multiple = true
  ]),

  /**
   * The name of a registered modal to use as a picker for the input.
   */
  pickerType: T.string,

  /**
   * Custom configuration for the picker
   */
  picker: T.shape({
    url: T.oneOfType([T.string, T.array]),
    title: T.string,
    filters: T.arrayOf(T.shape({
      // list filter types
    }))
  }),

  card: T.any,
  icon: T.string,
  add: T.string,
  multiple: T.bool
}, {
  value: null,
  picker: {},
  multiple: false,
  card: DataMicro,
  add: trans('add', {}, 'actions')
})

export {
  EntityInput
}
