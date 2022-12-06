import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ContentSections, ContentSection} from '#/main/app/content/components/sections'

import {MODAL_USERS} from '#/main/community/modals/users'
import {UserList} from '#/main/community/user/components/list'
import {MODAL_GROUPS} from '#/main/community/modals/groups'
import {GroupList} from '#/main/community/group/components/list'

import {Role as RoleTypes} from '#/main/community/role/prop-types'
import {RolePage} from '#/main/community/role/components/page'
import {RoleMetrics} from '#/main/community/role/components/metrics'
import {selectors} from '#/main/community/tools/community/role/store/selectors'

const RoleShow = (props) =>
  <RolePage
    path={props.path}
    role={props.role}
    reload={props.reload}
  >
    {get(props.role, 'id') &&
      <RoleMetrics
        load={(year) => props.loadMetrics(props.role.id, year)}
      />
    }

    {get(props.role, 'meta.description') &&
      <div className="panel panel-default">
        <div className="panel-body">{get(props.role, 'meta.description')}</div>
      </div>
    }

    <ContentSections level={3} defaultOpened="role-users">
      <ContentSection
        id="role-users"
        className="embedded-list-section"
        icon="fa fa-fw fa-user"
        title={trans('users', {}, 'community')}
        disabled={!props.role.id}
        actions={[
          {
            name: 'add-users',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_user'),
            displayed: hasPermission('edit', props.role),
            modal: [MODAL_USERS, {
              selectAction: (selected) => ({
                type: CALLBACK_BUTTON,
                label: trans('add', {}, 'actions'),
                callback: () => props.addUsers(props.role.id, selected)
              })
            }]
          }
        ]}
      >
        <UserList
          name={`${selectors.FORM_NAME}.users`}
          url={['apiv2_role_list_users', {id: props.role.id}]}
          autoload={!!props.role.id}
          delete={{
            url: ['apiv2_role_remove_users', {id: props.role.id}],
            displayed: () => hasPermission('edit', props.role)
          }}
          actions={undefined}
        />
      </ContentSection>

      <ContentSection
        id="role-groups"
        className="embedded-list-section"
        icon="fa fa-fw fa-users"
        title={trans('groups', {}, 'community')}
        disabled={!props.role.id}
        actions={[
          {
            name: 'add-groups',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_group'),
            displayed: hasPermission('edit', props.role),
            modal: [MODAL_GROUPS, {
              selectAction: (selected) => ({
                type: CALLBACK_BUTTON,
                label: trans('add', {}, 'actions'),
                callback: () => props.addGroups(props.role.id, selected)
              })
            }]
          }
        ]}
      >
        <GroupList
          name={`${selectors.FORM_NAME}.groups`}
          url={['apiv2_role_list_groups', {id: props.role.id}]}
          autoload={!!props.role.id}
          delete={{
            url: ['apiv2_role_remove_groups', {id: props.role.id}],
            displayed: () => hasPermission('edit', props.role)
          }}
          actions={undefined}
        />
      </ContentSection>
    </ContentSections>
  </RolePage>

RoleShow.propTypes = {
  path: T.string.isRequired,
  role: T.shape(
    RoleTypes.propTypes
  ),
  reload: T.func.isRequired,
  loadMetrics: T.func.isRequired,
  addUsers: T.func.isRequired,
  addGroups: T.func.isRequired
}

export {
  RoleShow
}
