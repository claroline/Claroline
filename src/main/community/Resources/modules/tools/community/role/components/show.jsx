import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {route} from '#/main/community/role/routing'
import {hasPermission} from '#/main/app/security'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {PageSection} from '#/main/app/page/components/section'
import {PageTabbedSection} from '#/main/app/page/components/tabbed-section'
import {ContentHtml} from '#/main/app/content/components/html'
import {DetailsData} from '#/main/app/content/details/containers/data'
import {ContentSections} from '#/main/app/content/components/sections'

import {MODAL_USERS} from '#/main/community/modals/users'
import {UserList} from '#/main/community/user/components/list'
import {MODAL_GROUPS} from '#/main/community/modals/groups'
import {GroupList} from '#/main/community/group/components/list'

import {constants} from '#/main/community/constants'
import {Role as RoleTypes} from '#/main/community/role/prop-types'
import {RolePage} from '#/main/community/role/components/page'
import {selectors} from '#/main/community/tools/community/role/store/selectors'
import {RoleRights} from '#/main/community/tools/community/role/components/rights'
import {Alert} from '#/main/app/components/alert'

const RoleShow = (props) =>
  <RolePage
    path={props.path}
    role={props.role}
    reload={(role) => props.reload(role, props.contextData)}
  >
    {get(props.role, 'meta.description') &&
      <PageSection size="md" className="pb-5">
        <ContentHtml className="lead">{get(props.role, 'meta.description')}</ContentHtml>
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
                name: 'name',
                type: 'string',
                label: trans('code')
              }, {
                name: 'type',
                type: 'choice',
                label: trans('type'),
                displayed: (role) => 'desktop' === props.contextType || constants.ROLE_PLATFORM === role.type,
                options: {
                  choices: constants.ROLE_TYPES
                },
                linked: [
                  {
                    name: 'workspace',
                    type: 'workspace',
                    label: trans('workspace'),
                    displayed: (role) => constants.ROLE_WORKSPACE === role.type
                  }, {
                    name: 'user',
                    type: 'user',
                    label: trans('user'),
                    displayed: (role) => constants.ROLE_USER === role.type
                  }
                ]
              }
            ]
          }
        ]}
      />
    </PageSection>

    <PageSection size="md">
      {'ROLE_ADMIN' === props.role.name &&
        <Alert className="my-3" type="warning" title={trans('Les utilisateurs possédant le rôle administrateur ne sont pas soumis à la gestion de droits.')}>
          {trans('Ils peuvent tout voir et tout faire sans restrictions. Vous ne devriez donner ce rôle qu\'à un nombre limité de personnes.')}
        </Alert>
      }

      {'ROLE_ADMIN' !== props.role.name &&
        <ContentSections level={3} defaultOpened="role-users" className="my-3">
          {props.role.id && ('workspace' === props.contextType || constants.ROLE_WORKSPACE === props.role.type) &&
            <RoleRights
              id="role-workspace-rights"
              disabled={!props.role.id}
              icon="fa fa-fw fa-lock"
              title={trans('permissions')}
              subtitle={trans('Donnez ou retirez des droits d\'accès aux détenteurs de ce rôle')}
              role={props.role}
              contextType="workspace"
              contextId={props.contextData ? props.contextData.id : get(props.role, 'workspace.id')}
              rights={props.workspaceRights}
              reload={props.loadWorkspaceRights}
              fill={true}
            />
          }

          {props.role.id && ('desktop' === props.contextType && constants.ROLE_WORKSPACE !== props.role.type) &&
            <RoleRights
              id="role-desktop-rights"
              disabled={!props.role.id}
              icon="fa fa-fw fa-lock"
              title={trans('permissions')}
              subtitle={trans('Donnez ou retirez des droits d\'accès aux détenteurs de ce rôle')}
              role={props.role}
              contextType="desktop"
              rights={props.desktopRights}
              reload={props.loadDesktopRights}
              fill={true}
            />
          }
        </ContentSections>
      }
    </PageSection>

    {'ROLE_ANONYMOUS' !== props.role.name &&
      <PageTabbedSection
        size="md"
        className="py-3 embedded-list-section"
        path={route(props.role, props.path)}
        tabs={[
          {
            path: '',
            exact: true,
            icon: 'fa fa-user',
            title: trans('users', {}, 'community'),
            render: () => (
              <UserList
                className="mt-3"
                path={props.path}
                name={`${selectors.FORM_NAME}.users`}
                url={['apiv2_role_list_users', {id: props.role.id}]}
                autoload={!!props.role.id}
                addAction={{
                  name: 'add-users',
                  type: MODAL_BUTTON,
                  icon: 'fa fa-fw fa-plus',
                  label: trans('add_users'),
                  tooltip: 'bottom',
                  displayed: hasPermission('edit', props.role) && ('workspace' !== props.contextType || constants.ROLE_PLATFORM !== props.role.type),
                  modal: [MODAL_USERS, {
                    selectAction: (selected) => ({
                      type: CALLBACK_BUTTON,
                      label: trans('add', {}, 'actions'),
                      callback: () => props.addUsers(props.role.id, selected)
                    })
                  }]
                }}
                delete={{
                  url: ['apiv2_role_remove_users', {id: props.role.id}],
                  displayed: () => (hasPermission('edit', props.role) && ('workspace' !== props.contextType || constants.ROLE_PLATFORM !== props.role.type))
                }}
                actions={undefined}
              />
            )
          }, {
            path: '/groups',
            icon: 'fa fa-users',
            title: trans('groups', {}, 'community'),
            render: () => (
              <GroupList
                className="mt-3"
                path={props.path}
                name={`${selectors.FORM_NAME}.groups`}
                url={['apiv2_role_list_groups', {id: props.role.id}]}
                autoload={!!props.role.id}
                addAction={{
                  name: 'add-groups',
                  type: MODAL_BUTTON,
                  icon: 'fa fa-fw fa-plus',
                  label: trans('add_groups'),
                  tooltip: 'bottom',
                  displayed: hasPermission('edit', props.role) && ('workspace' !== props.contextType || constants.ROLE_PLATFORM !== props.role.type),
                  modal: [MODAL_GROUPS, {
                    selectAction: (selected) => ({
                      type: CALLBACK_BUTTON,
                      label: trans('add', {}, 'actions'),
                      callback: () => props.addGroups(props.role.id, selected)
                    })
                  }]
                }}
                delete={{
                  url: ['apiv2_role_remove_groups', {id: props.role.id}],
                  displayed: () => (hasPermission('edit', props.role) && ('workspace' !== props.contextType || constants.ROLE_PLATFORM !== props.role.type))
                }}
                actions={undefined}
              />
            )
          }
        ]}
      />
    }
  </RolePage>

RoleShow.propTypes = {
  path: T.string.isRequired,
  role: T.shape(
    RoleTypes.propTypes
  ),
  contextType: T.string.isRequired,
  contextData: T.object,

  workspaceRights: T.object,
  desktopRights: T.object,
  administrationRights: T.object,

  reload: T.func.isRequired,
  loadWorkspaceRights: T.func.isRequired,
  loadDesktopRights: T.func.isRequired,
  addUsers: T.func.isRequired,
  addGroups: T.func.isRequired
}

export {
  RoleShow
}
