import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans, transChoice} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool'

import {getPlatformRoles, getWorkspaceRoles} from '#/main/community/utils'

import {GroupList as BaseGroupList} from '#/main/community/group/components/list'
import {selectors} from '#/main/community/tools/community/group/store/selectors'
import {MODAL_REGISTER} from '#/main/community/modals/register'
import {PageListSection} from '#/main/app/page/components/list-section'

const GroupList = props =>
  <ToolPage
    title={trans('groups', {}, 'community')}
  >
    <PageListSection>
      <BaseGroupList
        flush={true}
        path={props.path}
        name={selectors.LIST_NAME}
        url={!isEmpty(props.contextData) ?
          ['apiv2_workspace_list_groups', {id: props.contextData.id}]:
          ['apiv2_group_list']
        }
        addAction={'desktop' === props.contextType ?
          {
            name: 'add',
            type: LINK_BUTTON,
            // icon: 'fa fa-fw fa-plus',
            label: trans('add_group', {}, 'actions'),
            target: `${props.path}/groups/new`,
            displayed: props.canEdit
          } : {
            name: 'add',
            type: MODAL_BUTTON,
            label: trans('register_groups'),
            // icon: 'fa fa-fw fa-plus',
            displayed: props.canRegister,
            // select groups to register
            modal: [MODAL_REGISTER, {
              title: trans('register_groups'),
              subtitle: trans('workspace_register_select_groups'),
              workspaces: [props.contextData],
              onRegister: props.registerGroups,
              mode: 'groups'
            }]
          }
        }
        customActions={(rows) => !isEmpty(props.contextData) ? [
          {
            name: 'unregister',
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-user-minus',
            label: trans('unregister', {}, 'actions'),
            callback: () => props.unregisterGroups(rows, props.contextData),
            displayed: props.canRegister,
            confirm: {
              message: transChoice('group_unregister_confirm_message', rows.length, {count: '<b class="fw-bold">'+rows.length+'</b>'}, 'community'),
              items:  rows.map(item => ({
                thumbnail: item.thumbnail,
                name: item.name
              }))
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
    </PageListSection>
  </ToolPage>

GroupList.propTypes = {
  path: T.string.isRequired,
  contextType: T.string.isRequired,
  contextData: T.object,
  canRegister: T.bool.isRequired,
  canEdit: T.bool.isRequired,
  unregisterGroups: T.func.isRequired,
  registerGroups: T.func.isRequired
}

export {
  GroupList
}
