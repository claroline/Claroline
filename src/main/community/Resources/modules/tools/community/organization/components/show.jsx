import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {ContentSection, ContentSections} from '#/main/app/content/components/sections'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {UserList} from '#/main/community/user/components/list'
import {MODAL_USERS} from '#/main/community/modals/users'
import {GroupList} from '#/main/community/group/components/list'
import {MODAL_GROUPS} from '#/main/community/modals/groups'
import {WorkspaceList} from '#/main/core/workspace/components/list'
import {MODAL_WORKSPACES} from '#/main/core/modals/workspaces'

import {Organization as OrganizationTypes} from '#/main/community/organization/prop-types'
import {OrganizationPage} from '#/main/community/organization/components/page'
import {selectors} from '#/main/community/tools/community/organization/store'
import get from 'lodash/get'

const OrganizationShow = props =>
  <OrganizationPage
    path={props.path}
    organization={props.organization}
    reload={props.reload}
  >
    {get(props.organization, 'meta.description') &&
      <div className="panel panel-default">
        <div className="panel-body">{get(props.organization, 'meta.description')}</div>
      </div>
    }

    <ContentSections level={3}>
      <ContentSection
        className="embedded-list-section"
        icon="fa fa-fw fa-user-cog"
        title={trans('managers')}
        disabled={!props.organization.id}
        actions={[
          {
            name: 'add',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_managers'),
            displayed: hasPermission('edit', props.organization),
            modal: [MODAL_USERS, {
              selectAction: (users) => ({
                type: CALLBACK_BUTTON,
                label: trans('add', {}, 'actions'),
                callback: () => props.addManagers(props.organization.id, users.map(user => user.id))
              })
            }]
          }
        ]}
      >
        <UserList
          name={`${selectors.FORM_NAME}.managers`}
          url={['apiv2_organization_list_managers', {id: props.organization.id}]}
          autoload={!!props.organization.id}
          delete={{
            url: ['apiv2_organization_remove_managers', {id: props.organization.id}]
          }}
          actions={undefined}
        />
      </ContentSection>

      <ContentSection
        className="embedded-list-section"
        icon="fa fa-fw fa-book"
        title={trans('workspaces')}
        disabled={!props.organization.id}
        actions={[
          {
            name: 'add',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_workspaces'),
            displayed: hasPermission('edit', props.organization),
            modal: [MODAL_WORKSPACES, {
              url: ['apiv2_workspace_list'],
              selectAction: (workspaces) => ({
                type: CALLBACK_BUTTON,
                label: trans('add', {}, 'actions'),
                callback: () => props.addWorkspaces(props.organization.id, workspaces.map(workspace => workspace.id))
              })
            }]
          }
        ]}
      >
        <WorkspaceList
          name={`${selectors.FORM_NAME}.workspaces`}
          url={['apiv2_organization_list_workspaces', {id: props.organization.id}]}
          autoload={!!props.organization.id}
          delete={{
            url: ['apiv2_organization_remove_workspaces', {id: props.organization.id}]
          }}
          actions={undefined}
        />
      </ContentSection>

      <ContentSection
        className="embedded-list-section"
        icon="fa fa-fw fa-user"
        title={trans('users', {}, 'community')}
        disabled={!props.organization.id}
        actions={[
          {
            name: 'add',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_user'),
            displayed: hasPermission('edit', props.organization),
            modal: [MODAL_USERS, {
              selectAction: (users) => ({
                type: CALLBACK_BUTTON,
                label: trans('add', {}, 'actions'),
                callback: () => props.addUsers(props.organization.id, users.map(user => user.id))
              })
            }]
          }
        ]}
      >
        <UserList
          name={`${selectors.FORM_NAME}.users`}
          url={['apiv2_organization_list_users', {id: props.organization.id}]}
          autoload={!!props.organization.id}
          delete={{
            url: ['apiv2_organization_remove_users', {id: props.organization.id}]
          }}
          actions={undefined}
        />
      </ContentSection>

      <ContentSection
        icon="fa fa-fw fa-users"
        className="embedded-list-section"
        title={trans('groups', {}, 'community')}
        disabled={!props.organization.id}
        actions={[
          {
            name: 'add',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_group'),
            displayed: hasPermission('edit', props.organization),
            modal: [MODAL_GROUPS, {
              selectAction: (groups) => ({
                type: CALLBACK_BUTTON,
                label: trans('add', {}, 'actions'),
                callback: () => props.addGroups(props.organization.id, groups.map(group => group.id))
              })
            }]
          }
        ]}
      >
        <GroupList
          name={`${selectors.FORM_NAME}.groups`}
          url={['apiv2_organization_list_groups', {id: props.organization.id}]}
          autoload={!!props.organization.id}
          delete={{
            url: ['apiv2_organization_remove_groups', {id: props.organization.id}]
          }}
          actions={undefined}
        />
      </ContentSection>
    </ContentSections>
  </OrganizationPage>

OrganizationShow.propTypes = {
  path: T.string.isRequired,
  organization: T.shape(
    OrganizationTypes.propTypes
  ).isRequired,
  reload: T.func.isRequired,
  addUsers: T.func.isRequired,
  addGroups: T.func.isRequired,
  addWorkspaces: T.func.isRequired,
  addManagers: T.func.isRequired
}

export {
  OrganizationShow
}
