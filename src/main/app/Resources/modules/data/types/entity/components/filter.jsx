import React from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataSearch as DataSearchTypes} from '#/main/app/data/types/prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

const EntityFilter = (props) =>
  <span className="data-filter entity-filter">
    {props.search}

    <Button
      className="btn btn-outline-secondary btn-filter"
      type={MODAL_BUTTON}
      tooltip="left"
      icon={props.icon}
      label={props.placeholder || trans('select', {}, 'actions')}
      size="sm"
      modal={[props.picker.type, {
        ...props.picker,
        selectAction: (selected) => ({
          type: CALLBACK_BUTTON,
          label: trans('select', {}, 'actions'),
          callback: () => props.updateSearch(selected[0].id)
        })
      }]}
      disabled={props.disabled}
    />
  </span>

implementPropTypes(EntityFilter, DataSearchTypes, {
  search: T.string,
  picker: T.shape({
    type: T.string.isRequired,
    url: T.oneOfType([T.string, T.array]),
    title: T.string,
    filters: T.arrayOf(T.shape({
      // list filter types
    }))
  }).isRequired,
  icon: T.string
})

export {
  EntityFilter
}