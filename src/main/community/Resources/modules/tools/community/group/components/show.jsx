import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {ContentSection, ContentSections} from '#/main/app/content/components/sections'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Alert} from '#/main/app/alert/components/alert'

import {MODAL_USERS} from '#/main/community/modals/users'
import {UserList} from '#/main/community/user/components/list'
import {MODAL_ROLES} from '#/main/community/modals/roles'
import {RoleList} from '#/main/community/role/components/list'

import {Group as GroupTypes} from '#/main/community/group/prop-types'
import {selectors} from '#/main/community/tools/community/group/store/selectors'
import {GroupPage} from '#/main/community/group/components/page'

const GroupShow = (props) =>
  <GroupPage
    path={props.path}
    group={props.group}
    reload={props.reload}
  >
    {get(props.group, 'meta.description') &&
      <div className="panel panel-default">
        <div className="panel-body">{get(props.group, 'meta.description')}</div>
      </div>
    }

    {hasPermission('administrate', props.group) && get(props.group, 'meta.readOnly') &&
      <Alert type="info">
        {trans('group_locked', {}, 'community')}
      </Alert>
    }

    <ContentSections level={3} defaultOpened="group-users">
      <ContentSection
        id="group-users"
        className="embedded-list-section"
        icon="fa fa-fw fa-user"
        title={trans('users', {}, 'community')}
        disabled={!props.group.id}
        actions={[
          {
            name: 'add-users',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_users'),
            displayed: hasPermission('administrate', props.group),
            disabled: get(props.group, 'meta.readOnly'),
            modal: [MODAL_USERS, {
              selectAction: (selected) => ({
                type: CALLBACK_BUTTON,
                label: trans('add', {}, 'actions'),
                callback: () => props.addUsers(props.group.id, selected)
              })
            }]
          }
        ]}
      >
        <UserList
          name={`${selectors.FORM_NAME}.users`}
          url={['apiv2_group_list_users', {id: props.group.id}]}
          autoload={!!props.group.id}
          delete={{
            url: ['apiv2_group_remove_users', {id: props.group.id}],
            disabled: () => get(props.group, 'meta.readOnly'),
            displayed: () => hasPermission('administrate', props.group)
          }}
          actions={undefined}
        />
      </ContentSection>

      {hasPermission('administrate', props.group) &&
        <ContentSection
          id="group-roles"
          className="embedded-list-section"
          icon="fa fa-fw fa-id-badge"
          title={trans('roles', {}, 'community')}
          disabled={!props.group.id}
          actions={[
            {
              name: 'add-roles',
              type: MODAL_BUTTON,
              icon: 'fa fa-fw fa-plus',
              label: trans('add_roles'),
              disabled: get(props.group, 'meta.readOnly'),
              modal: [MODAL_ROLES, {
                selectAction: (selected) => ({
                  type: CALLBACK_BUTTON,
                  label: trans('add', {}, 'actions'),
                  callback: () => props.addRoles(props.group.id, selected)
                })
              }]
            }
          ]}
        >
          <RoleList
            name={`${selectors.FORM_NAME}.roles`}
            url={['apiv2_group_list_roles', {id: props.group.id}]}
            autoload={!!props.group.id}
            delete={{
              url: ['apiv2_group_remove_roles', {id: props.group.id}],
              disabled: () => get(props.group, 'meta.readOnly')
            }}
            actions={undefined}
          />
        </ContentSection>
      }
    </ContentSections>
  </GroupPage>

GroupShow.propTypes = {
  path: T.string.isRequired,
  group: T.shape(
    GroupTypes.propTypes
  ),
  reload: T.func.isRequired,
  addUsers: T.func.isRequired,
  addRoles: T.func.isRequired
}

export {
  GroupShow
}
