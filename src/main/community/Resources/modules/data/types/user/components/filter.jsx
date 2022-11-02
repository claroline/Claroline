import React from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataSearch as DataSearchTypes} from '#/main/app/data/types/prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_USERS} from '#/main/community/modals/users'

const UserFilter = (props) =>
  <span className="data-filter user-filter">
    {props.search}

    <Button
      className="btn btn-filter"
      type={MODAL_BUTTON}
      tooltip="left"
      icon="fa fa-fw fa-user"
      label={props.placeholder || trans('select', {}, 'actions')}
      size="sm"
      modal={[MODAL_USERS, {
        selectAction: (selected) => ({
          type: CALLBACK_BUTTON,
          label: trans('select', {}, 'actions'),
          callback: () => props.updateSearch(selected[0].id)
        })
      }]}
      disabled={props.disabled}
    />
  </span>

implementPropTypes(UserFilter, DataSearchTypes, {
  /*search: T.shape({
   id: T.string.isRequired,
   name: T.string.isRequired
   }),*/
  search: T.string
})

export {
  UserFilter
}
