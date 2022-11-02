import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {MODAL_GROUPS} from '#/main/community/modals/groups'
import {MODAL_ROLES} from '#/main/community/modals/roles'

import {Groups} from '#/main/community/tools/community/group/components/groups'
import {Group} from '#/main/community/tools/community/group/components/group'
import {constants} from '#/main/community/constants'

const GroupTab = props =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('groups'),
      target: `${props.path}/groups`
    }]}
    subtitle={trans('groups')}
    primaryAction="register_groups"
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
          title: trans('register_groups'),
          subtitle: trans('workspace_register_select_groups'),
          selectAction: (selectedGroups) => ({
            type: MODAL_BUTTON,
            label: trans('select', {}, 'actions'),

            // select roles to assign to selected groups
            modal: [MODAL_ROLES, {
              url: ['apiv2_workspace_list_roles', {id: get(props.contextData, 'id')}],
              filters: [
                // those filters are not exploited as the url already do it for us. This is just to disable filters
                {property: 'type', value: constants.ROLE_WORKSPACE, locked: true},
                {property: 'workspace', value: get(props.contextData, 'id'), locked: true, hidden: true}
              ],
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
          path: '/groups/:id',
          component: Group,
          onEnter: (params) => props.open(params.id)
        }
      ]}
    />
  </ToolPage>

GroupTab.propTypes = {
  path: T.string.isRequired,
  contextData: T.object,

  canRegister: T.bool.isRequired,
  open: T.func.isRequired,
  addGroupsToRoles: T.func.isRequired
}

export {
  GroupTab
}
