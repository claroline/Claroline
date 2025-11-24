import React from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataSearch as DataSearchTypes} from '#/main/app/data/types/prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

const EntityFilter = (props) =>
  <span className="data-filter entity-filter">
    {props.value}

    <Button
      className="btn btn-body btn-filter"
      type={MODAL_BUTTON}
      tooltip="left"
      icon={props.icon}
      label={props.placeholder || trans('select', {}, 'actions')}
      size={props.size}
      modal={[props.pickerType, {
        ...props.picker,
        multiple: props.multiple,
        selectAction: (selected) => ({
          type: CALLBACK_BUTTON,
          label: trans('select', {}, 'actions'),
          callback: () => {
            if (props.multiple) {
              props.updateSearch(selected.map(s => s.id))
            } else {
              props.updateSearch(selected[0].id)
            }
          }
        })
      }]}
      disabled={props.disabled}
    />
  </span>

implementPropTypes(EntityFilter, DataSearchTypes, {
  search: T.string,
  pickerType: T.string.isRequired,
  picker: T.shape({
    url: T.oneOfType([T.string, T.array]),
    title: T.string,
    filters: T.arrayOf(T.shape({
      // list filter types
    }))
  }),
  multiple: T.bool,
  icon: T.string
}, {
  multiple: false // for retro-compatibility. should be true like EntityInput
})

export {
  EntityFilter
}
