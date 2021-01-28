import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'

import {constants} from '#/main/core/user/constants'
import {RoleCard} from '#/main/core/user/data/components/role-card'
import {selectors} from '#/main/core/tools/community/role/store'

const RolesList = props =>
  <ListData
    name={selectors.LIST_NAME}
    fetch={{
      url: ['apiv2_workspace_list_roles_configurable', {id: props.workspace.id}],
      autoload: true
    }}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: `${props.path}/roles/form/${row.id}`
    })}
    delete={{
      url: ['apiv2_role_delete_bulk'],
      disabled: rows => !!rows.find(row => row.name && (row.name.indexOf('ROLE_WS_COLLABORATOR_') > -1 || row.name.indexOf('ROLE_WS_MANAGER_') > -1))
    }}
    definition={[
      {
        name: 'translationKey',
        type: 'translation',
        label: trans('name'),
        displayed: true,
        primary: true
      }, {
        name: 'name',
        type: 'string',
        label: trans('code'),
        displayed: false
      }, {
        name: 'type',
        type: 'choice',
        label: trans('type'),
        options: {
          choices: constants.ROLE_TYPES
        },
        displayed: false
      }
    ]}
    card={RoleCard}
  />

RolesList.propTypes = {
  path: T.string.isRequired,
  workspace: T.shape(WorkspaceTypes.propTypes)
}

const Roles = connect(
  state => ({
    path: toolSelectors.path(state),
    workspace: toolSelectors.contextData(state)
  })
)(RolesList)

export {
  Roles
}
