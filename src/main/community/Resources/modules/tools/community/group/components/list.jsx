import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans, transChoice} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool'
import {PageListSection} from '#/main/app/page'

import {getPlatformRoles, getWorkspaceRoles} from '#/main/community/utils'

import {GroupList as BaseGroupList} from '#/main/community/group/components/list'
import {selectors} from '#/main/community/tools/community/group/store/selectors'
import {MODAL_REGISTER} from '#/main/community/modals/register'
import {MODAL_GROUP_FORM} from '#/main/community/group/modals/form'

const GroupList = props =>
  <ToolPage
    title={trans('groups', {}, 'community')}
  >
    <PageListSection
      poster={props.poster}
      title={trans('groups', {}, 'community')}
      addAction={'desktop' === props.contextType ?
        {
          name: 'add',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-plus',
          label: trans('add_group', {}, 'actions'),
          displayed: props.canEdit,
          modal: [MODAL_GROUP_FORM, {
            isNew: true,
            onSave: props.invalidateList
          }]
        } : {
          name: 'add',
          type: MODAL_BUTTON,
          label: trans('register_groups'),
          icon: 'fa fa-fw fa-plus',
          displayed: props.canRegister,
          // select groups to register
          modal: [MODAL_REGISTER, {
            title: trans('register_groups'),
            subtitle: trans('workspace_register_select_groups'),
            workspace: props.contextData,
            onRegister: props.registerGroups,
            mode: 'groups'
          }]
        }
      }
    >
      <BaseGroupList
        className="mb-5"
        flush={true}
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
            callback: () => props.unregisterGroups(rows, props.contextData),
            displayed: props.canRegister,
            confirm: {
              message: transChoice('group_unregister_confirm_message', rows.length, {count: '<b class="fw-bold">'+rows.length+'</b>'}, 'community'),
              items:  rows.map(item => ({
                thumbnail: item.thumbnail,
                id: item.id,
                name: item.name
              }))
            },
            dangerous: true
          }] : []
        }
        customDefinition={[
          {
            name: 'roles',
            type: 'role',
            label: trans('roles'),
            calculated: (group) => !isEmpty(props.contextData) ?
              getWorkspaceRoles(group.roles, props.contextData.id) :
              getPlatformRoles(group.roles),
            displayed: true,
            filterable: true,
            sortable: false,
            options: {
              multiple: true,
              picker: {
                personal: false,
                contextType: props.contextType,
                contextId: !isEmpty(props.contextData) ? props.contextData.id : null
              }
            }
          }
        ]}
      />
    </PageListSection>
  </ToolPage>

GroupList.propTypes = {
  path: T.string.isRequired,
  poster: T.string,
  contextType: T.string.isRequired,
  contextData: T.object,
  canRegister: T.bool.isRequired,
  canEdit: T.bool.isRequired,
  unregisterGroups: T.func.isRequired,
  registerGroups: T.func.isRequired,
  invalidateList: T.func.isRequired
}

export {
  GroupList
}
