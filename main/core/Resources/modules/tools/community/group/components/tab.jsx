import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {MODAL_GROUPS} from '#/main/core/modals/groups'
import {MODAL_ROLES} from '#/main/core/modals/roles'

import {Groups} from '#/main/core/tools/community/group/components/groups'
import {Group} from '#/main/core/tools/community/group/components/group'

const GroupTab = props =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('groups'),
      target: `${props.path}/groups`
    }]}
    subtitle={trans('groups')}
    actions={[
      {
        name: 'register_groups',
        type: MODAL_BUTTON,
        label: trans('register_groups'),
        icon: 'fa fa-plus',
        primary: true,
        displayed: props.canRegister,

        // select groups to register
        modal: [MODAL_GROUPS, {
          url: ['apiv2_group_list_registerable'],
          title: trans('register_groups'),
          subtitle: trans('workspace_register_select_groups'),
          selectAction: (selectedGroups) => ({
            type: MODAL_BUTTON,
            label: trans('select', {}, 'actions'),

            // select roles to assign to selected groups
            modal: [MODAL_ROLES, {
              url: ['apiv2_workspace_list_roles', {id: get(props.contextData, 'uuid')}],
              filters: [],
              title: trans('register_groups'),
              subtitle: trans('workspace_register_select_roles'),
              selectAction: (selectedRoles) => ({
                type: CALLBACK_BUTTON,
                label: trans('register', {}, 'actions'),
                callback: () => props.addGroupsToRoles(selectedRoles, selectedGroups)
              })
            }]
          })
        }]
      }, {
        name: 'create_group',
        type: LINK_BUTTON,
        label: trans('create_group'),
        icon: 'fa fa-pencil',
        target: `${props.path}/groups/form`,
        displayed: props.canCreate
      }
    ]}
  >
    <Routes
      path={props.path}
      routes={[
        {
          path: '/groups',
          exact: true,
          component: Groups
        }, {
          path: '/groups/form/:id?',
          component: Group,
          onEnter: (params) => props.open(params.id || null, props.defaultRole)
        }
      ]}
    />
  </ToolPage>

GroupTab.propTypes = {
  path: T.string.isRequired,
  contextData: T.object,

  canCreate: T.bool.isRequired,
  canRegister: T.bool.isRequired,
  open: T.func.isRequired,
  addGroupsToRoles: T.func.isRequired,
  defaultRole: T.object
}

export {
  GroupTab
}
