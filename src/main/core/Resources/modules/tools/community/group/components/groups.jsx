import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

import {trans, transChoice} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {GroupCard} from '#/main/core/user/data/components/group-card'
import {constants} from '#/main/core/user/constants'
import {actions, selectors} from '#/main/core/tools/community/group/store'

const GroupsList = props =>
  <ListData
    name={selectors.LIST_NAME}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: `${props.path}/groups/${row.id}`
    })}
    fetch={{
      url: ['apiv2_workspace_list_groups', {id: props.workspace.id}],
      autoload: true
    }}
    actions={(rows) => !isEmpty(props.workspace) ? [
      {
        name: 'unregister',
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-trash-o',
        label: trans('unregister', {}, 'actions'),
        callback: () => props.unregister(rows, props.workspace),
        confirm: {
          title: trans('unregister_groups'),
          message: transChoice('unregister_groups_confirm_message', rows.length, {count: rows.length})
        },
        dangerous: true
      }] : []}
    definition={[
      {
        name: 'name',
        type: 'string',
        label: trans('name'),
        displayed: true,
        primary: true
      }, {
        name: 'roles',
        alias: 'role',
        type: 'roles',
        label: trans('roles'),
        calculated: (group) => !isEmpty(props.workspace) ?
          group.roles.filter(role => role.workspace && role.workspace.id === props.workspace.id)
          :
          group.roles.filter(role => constants.ROLE_PLATFORM === role.type),
        displayed: true,
        filterable: true
      }
    ]}
    card={GroupCard}
  />

GroupsList.propTypes = {
  path: T.string.isRequired,
  workspace: T.object,
  unregister: T.func
}

const Groups = connect(
  state => ({
    path: toolSelectors.path(state),
    workspace: toolSelectors.contextData(state)
  }),
  dispatch => ({
    unregister(users, workspace) {
      dispatch(actions.unregister(users, workspace))
    }
  })
)(GroupsList)

export {
  Groups
}
