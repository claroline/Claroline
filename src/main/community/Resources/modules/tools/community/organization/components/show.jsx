import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {DetailsData} from '#/main/app/content/details/containers/data'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action'
import {PageTabbedSection} from '#/main/app/page/components/tabbed-section'

import {UserList} from '#/main/community/user/components/list'
import {MODAL_USERS} from '#/main/community/modals/users'
import {GroupList} from '#/main/community/group/components/list'
import {MODAL_GROUPS} from '#/main/community/modals/groups'
import {WorkspaceList} from '#/main/core/workspace/components/list'
import {MODAL_WORKSPACES} from '#/main/core/modals/workspaces'

import {Organization as OrganizationTypes} from '#/main/community/organization/prop-types'
import {OrganizationPage} from '#/main/community/organization/components/page'
import {selectors} from '#/main/community/tools/community/organization/store'
import {PageSection} from '#/main/app/page/components/section'
import {ContentHtml} from '#/main/app/content/components/html'
import {route} from '#/main/community/organization/routing'
import {ContentInfoBlocks} from '#/main/app/content/components/info-block'
import {displayDate, now} from '#/main/app/intl'

const OrganizationShow = props =>
  <OrganizationPage
    path={props.path}
    organization={props.organization}
    reload={props.reload}
  >
    {get(props.organization, 'meta.description') &&
      <PageSection size="md">
        <ContentHtml className="lead my-5">{get(props.organization, 'meta.description')}</ContentHtml>

        <ContentInfoBlocks
          className="mb-5"
          size="lg"
          items={[
            {
              icon: 'fa fa-book',
              label: trans('workspaces'),
              value: '100 espaces'
            }, {
              icon: 'fa fa-user',
              label: trans('members', {}, 'community'),
              value: '10 utilisateurs'
            }, {
              icon: 'fa fa-history',
              label: trans('last_activity'),
              value: displayDate(now(), false, true)
            }
          ]}
        />
      </PageSection>
    }

    <PageSection size="md" className="bg-body-tertiary">
      <DetailsData
        className="mt-3"
        name={selectors.FORM_NAME}
        definition={[
          {
            title: trans('general'),
            primary: true,
            fields: [
              {
                name: 'code',
                type: 'string',
                label: trans('code')
              }, {
                name: 'email',
                type: 'email',
                label: trans('email'),
                displayed: (organization) => !!organization.email
              }
            ]
          }
        ]}
      />
    </PageSection>

    <PageTabbedSection
      size="md"
      className="py-3"
      path={route(props.organization, props.path)}
      tabs={[
        {
          path: '',
          exact: true,
          icon: 'fa fa-user',
          title: trans('users', {}, 'community'),
          render: () => (
            <>
              {hasPermission('edit', props.organization) &&
                <Button
                  className="btn btn-primary my-3"
                  {...{
                    name: 'add',
                    type: MODAL_BUTTON,
                    label: trans('add_users'),
                    modal: [MODAL_USERS, {
                      selectAction: (users) => ({
                        type: CALLBACK_BUTTON,
                        label: trans('add', {}, 'actions'),
                        callback: () => props.addUsers(props.organization.id, users.map(user => user.id))
                      })
                    }]
                  }}
                />
              }

              <UserList
                path={props.path}
                name={`${selectors.FORM_NAME}.users`}
                url={['apiv2_organization_list_users', {id: props.organization.id}]}
                autoload={!!props.organization.id}
                delete={{
                  url: ['apiv2_organization_remove_users', {id: props.organization.id}],
                  displayed: () => hasPermission('edit', props.organization)
                }}
                actions={undefined}
              />
            </>
          )
        }, {
          path: '/managers',
          icon: 'fa fa-user-tie',
          title: trans('managers', {}, 'community'),
          render: () => (
            <>
              {hasPermission('edit', props.organization) &&
                <Button
                  className="btn btn-primary my-3"
                  {...{
                    name: 'add-managers',
                    type: MODAL_BUTTON,
                    label: trans('add_managers'),
                    modal: [MODAL_USERS, {
                      selectAction: (users) => ({
                        type: CALLBACK_BUTTON,
                        label: trans('add', {}, 'actions'),
                        callback: () => props.addManagers(props.organization.id, users.map(user => user.id))
                      })
                    }]
                  }}
                />
              }

              <UserList
                path={props.path}
                name={`${selectors.FORM_NAME}.managers`}
                url={['apiv2_organization_list_managers', {id: props.organization.id}]}
                autoload={!!props.organization.id}
                delete={{
                  url: ['apiv2_organization_remove_managers', {id: props.organization.id}],
                  displayed: () => hasPermission('edit', props.organization)
                }}
                actions={undefined}
              />
            </>
          )
        }, {
          path: '/groups',
          icon: 'fa fa-users',
          title: trans('groups', {}, 'community'),
          render: () => (
            <>
              {hasPermission('edit', props.organization) &&
                <Button
                  className="btn btn-primary my-3"
                  {...{
                    name: 'add',
                    type: MODAL_BUTTON,
                    label: trans('add_group'),
                    modal: [MODAL_GROUPS, {
                      selectAction: (groups) => ({
                        type: CALLBACK_BUTTON,
                        label: trans('add', {}, 'actions'),
                        callback: () => props.addGroups(props.organization.id, groups.map(group => group.id))
                      })
                    }]
                  }}
                />
              }

              <GroupList
                path={props.path}
                name={`${selectors.FORM_NAME}.groups`}
                url={['apiv2_organization_list_groups', {id: props.organization.id}]}
                autoload={!!props.organization.id}
                delete={{
                  url: ['apiv2_organization_remove_groups', {id: props.organization.id}],
                  displayed: () => hasPermission('edit', props.organization)
                }}
                actions={undefined}
              />
            </>
          )
        }, {
          path: '/workspaces',
          icon: 'fa fa-book',
          title: trans('workspaces'),
          render: () => (
            <>
              {hasPermission('edit', props.organization) &&
                <Button
                  className="btn btn-primary my-3"
                  {...{
                    name: 'add-workspace',
                    type: MODAL_BUTTON,
                    label: trans('add_workspaces'),
                    modal: [MODAL_WORKSPACES, {
                      url: ['apiv2_workspace_list'],
                      selectAction: (workspaces) => ({
                        type: CALLBACK_BUTTON,
                        label: trans('add', {}, 'actions'),
                        callback: () => props.addWorkspaces(props.organization.id, workspaces.map(workspace => workspace.id))
                      })
                    }]
                  }}
                />
              }

              <WorkspaceList
                name={`${selectors.FORM_NAME}.workspaces`}
                url={['apiv2_organization_list_workspaces', {id: props.organization.id}]}
                autoload={!!props.organization.id}
                delete={{
                  url: ['apiv2_organization_remove_workspaces', {id: props.organization.id}],
                  displayed: () => hasPermission('edit', props.organization)
                }}
                actions={undefined}
                customDefinition={[
                  {
                    name: 'meta.model',
                    label: trans('model'),
                    type: 'boolean',
                    alias: 'model'
                  }
                ]}
              />
            </>
          )
        }
      ]}
    />
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
