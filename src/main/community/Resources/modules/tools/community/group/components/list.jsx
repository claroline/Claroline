import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans, transChoice} from '#/main/app/intl/translation'
import {ASYNC_BUTTON, CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {MODAL_GROUPS} from '#/main/community/modals/groups'
import {getPlatformRoles, getWorkspaceRoles} from '#/main/community/utils'

import {GroupList as BaseGroupList} from '#/main/community/group/components/list'
import {selectors} from '#/main/community/tools/community/group/store/selectors'
import {GroupCard} from '#/main/community/group/components/card'

const GroupList = props =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('groups', {}, 'community'),
      target: `${props.path}/groups`
    }]}
    subtitle={trans('groups', {}, 'community')}
    primaryAction="add"
    actions={[
      {
        name: 'add',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('add_group', {}, 'actions'),
        target: `${props.path}/groups/new`,
        displayed: 'desktop' === props.contextType && props.canEdit,
        primary: true
      }, {
        name: 'add',
        type: MODAL_BUTTON,
        label: trans('register_groups'),
        icon: 'fa fa-fw fa-plus',
        primary: true,
        displayed: 'workspace' === props.contextType && props.canRegister,

        // select groups to register
        modal: [MODAL_GROUPS, {
          title: trans('register_groups'),
          subtitle: trans('workspace_register_select_groups'),
          selectAction: (selectedGroups) => ({
            type: ASYNC_BUTTON,
            label: trans('select', {}, 'actions'),
            request: {
              url: ['apiv2_workspace_register'],
              request: {
                method: 'PATCH',
                body: JSON.stringify({
                  workspaces: [props.contextData.id],
                  groups: selectedGroups.map(group => group.id)
                })
              },
              success: () => {
                props.registerGroups(selectedGroups)
              }
            }
          })
        }]
      }
    ]}
  >
    <BaseGroupList
      path={props.path}
      name={selectors.LIST_NAME}
      url={!isEmpty(props.contextData) ?
        ['apiv2_workspace_list_groups', {id: props.contextData.id}]:
        ['apiv2_group_list']
      }
      customActions={(rows) => !isEmpty(props.contextData) ? [
        {
          name: 'unregister',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-user-minus',
          label: trans('unregister', {}, 'actions'),
          callback: () => props.unregister(rows, props.contextData),
          displayed: props.canRegister,
          confirm: {
            title: transChoice('group_unregister_confirm_title', rows.length, {}, 'community'),
            subtitle: 1 === rows.length ? rows[0].name : transChoice('count_elements', rows.length, {count: rows.length}),
            message: transChoice('group_unregister_confirm_message', rows.length, {count: rows.length}, 'community'),
            additional: [
              createElement('div', {
                key: 'additional',
                className: 'modal-body'
              }, rows.map(group => createElement(GroupCard, {
                key: group.id,
                orientation: 'row',
                size: 'xs',
                data: group
              })))
            ]
          },
          dangerous: true
        }] : []
      }
      customDefinition={[
        {
          name: 'roles',
          type: 'roles',
          label: trans('roles'),
          calculated: (group) => !isEmpty(props.contextData) ?
            getWorkspaceRoles(group.roles, props.contextData.id) :
            getPlatformRoles(group.roles),
          displayed: true,
          filterable: true,
          sortable: false,
          options: {
            picker: !isEmpty(props.contextData) ? {
              url: ['apiv2_workspace_list_roles_configurable', {workspace: props.contextData.id}],
              filters: []
            } : undefined
          }
        }
      ]}
    />
  </ToolPage>

GroupList.propTypes = {
  path: T.string.isRequired,
  contextType: T.string.isRequired,
  contextData: T.object,
  canRegister: T.bool.isRequired,
  canEdit: T.bool.isRequired,
  unregister: T.func.isRequired,
  registerGroups: T.func.isRequired
}

export {
  GroupList
}
