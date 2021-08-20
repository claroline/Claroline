import React from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataSearch as DataSearchTypes} from '#/main/app/data/types/prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_WORKSPACES} from '#/main/core/modals/workspaces'

const WorkspacesFilter = (props) =>
  <span className="data-filter user-filter">
    {props.search ? props.search.join(', ') : ''}

    <Button
      className="btn btn-filter"
      type={MODAL_BUTTON}
      tooltip="left"
      icon="fa fa-fw fa-book"
      label={props.placeholder || trans('select', {}, 'actions')}
      size="sm"
      modal={[MODAL_WORKSPACES, {
        selectAction: (selected) => ({
          type: CALLBACK_BUTTON,
          label: trans('select', {}, 'actions'),
          callback: () => props.updateSearch(selected.map(workspace => workspace.id))
        })
      }]}
      disabled={props.disabled}
    />
  </span>

implementPropTypes(WorkspacesFilter, DataSearchTypes, {
  /*search: T.arrayOf(T.shape({
   id: T.string.isRequired,
   name: T.string.isRequired
   })),*/
  search: T.string
})

export {
  WorkspacesFilter
}
