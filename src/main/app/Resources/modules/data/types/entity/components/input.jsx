import React, {createElement} from 'react'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {DataMicro} from '#/main/app/data/components/micro'

const EntityPickerButton = props =>
  <Button
    className={props.className}
    type={MODAL_BUTTON}
    icon="fa fa-plus"
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

EntityPickerButton.propTypes = {
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
  help: T.string,
  onChange: T.func.isRequired,
  size: T.string,
  disabled: T.bool,
  multiple: T.bool
}

const EntityItem = ({entity, card, openAction, remove, disabled}) => {
  return (
    <>
      {createElement(card, {
        object: entity,
        className: 'fw-normal'
      })}

      {openAction &&
        <Button
          className="btn btn-link ms-auto me-n3"
          size="sm"
          label={trans('open', {}, 'actions')}
          {...openAction(entity)}
        />
      }

      <Button
        className={classes('btn btn-link me-n2', {'ms-auto': !openAction})}
        size="sm"
        {...{
          type: CALLBACK_BUTTON,
          label: trans('remove', {}, 'actions'),
          disabled: disabled,
          callback: remove
        }}
      />
    </>
  )
}

EntityItem.propTypes = {
  entity: T.object.isRequired,
  card: T.any.isRequired,
  openAction: T.func,
  remove: T.func.isRequired,
  disabled: T.bool
}

const EntityInput = (props) => {
  if (isEmpty(props.value)) {
    return (
      <div className="py-1" role="presentation">
        <EntityPickerButton
          className="btn-add btn btn-link text-start ms-n2 px-2"
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
            <EntityItem
              entity={object}
              card={props.card}
              openAction={props.openAction}
              disabled={props.disabled}
              remove={() => {
                const newValue = [].concat(props.value || [])
                const index = newValue.findIndex(o => o.id === object.id)

                if (-1 < index) {
                  newValue.splice(index, 1)
                  props.onChange(newValue)
                }
              }}
            />
          </li>
        ))}

        <li className="list-group-item py-1 px-0">
          <EntityPickerButton
            className="btn-add btn btn-link text-start ms-n2 px-2"
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
      <EntityItem
        entity={props.value}
        card={props.card}
        openAction={props.openAction}
        disabled={props.disabled}
        remove={() => props.onChange(null)}
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

  /**
   * A function which returns an Action definition. It takes in parameter the entity.
   */
  openAction: T.func,
  card: T.any,
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
