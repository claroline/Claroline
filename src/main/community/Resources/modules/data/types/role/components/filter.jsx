import React from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataSearch as DataSearchTypes} from '#/main/app/data/types/prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_ROLES} from '#/main/community/modals/roles'

const RoleFilter = (props) =>
  <span className="data-filter role-filter">
    {props.search}

    <Button
      className="btn btn-filter"
      type={MODAL_BUTTON}
      tooltip="left"
      icon="fa fa-fw fa-id-badge"
      label={props.placeholder || trans('select', {}, 'actions')}
      size="sm"
      modal={[MODAL_ROLES, {
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

implementPropTypes(RoleFilter, DataSearchTypes, {
  /*search: T.shape({
   id: T.string.isRequired,
   name: T.string.isRequired
   }),*/
  search: T.string,
  picker: T.shape({
    url: T.oneOfType([T.string, T.array]),
    title: T.string,
    filters: T.arrayOf(T.shape({
      // TODO : list filter types
    }))
  })
})

export {
  RoleFilter
}
