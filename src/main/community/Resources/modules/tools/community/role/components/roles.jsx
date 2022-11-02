import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {constants} from '#/main/community/constants'
import {RoleCard} from '#/main/core/user/data/components/role-card'
import {selectors} from '#/main/community/tools/community/role/store'

const Roles = props =>
  <ListData
    name={selectors.LIST_NAME}
    fetch={{
      url: ['apiv2_workspace_list_roles_configurable', {workspace: props.workspace.id}],
      autoload: true
    }}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: `${props.path}/roles/form/${row.id}`
    })}
    delete={{
      url: ['apiv2_role_delete_bulk'],
      disabled: rows => !!rows.find(row => constants.ROLE_WORKSPACE !== row.type || (row.name && (row.name.indexOf('ROLE_WS_COLLABORATOR_') > -1 || row.name.indexOf('ROLE_WS_MANAGER_') > -1)))
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

Roles.propTypes = {
  path: T.string,
  workspace: T.shape({
    id: T.string.isRequired
  })
}

export {
  Roles
}
