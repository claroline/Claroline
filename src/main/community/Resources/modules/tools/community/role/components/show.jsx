import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {DetailsData} from '#/main/app/content/details/containers/data'
import {ContentSections, ContentSection} from '#/main/app/content/components/sections'

import {MODAL_USERS} from '#/main/community/modals/users'
import {UserList} from '#/main/community/user/components/list'
import {MODAL_GROUPS} from '#/main/community/modals/groups'
import {GroupList} from '#/main/community/group/components/list'

import {constants} from '#/main/community/constants'
import {Role as RoleTypes} from '#/main/community/role/prop-types'
import {RolePage} from '#/main/community/role/components/page'
import {RoleMetrics} from '#/main/community/role/components/metrics'
import {selectors} from '#/main/community/tools/community/role/store/selectors'
import {RoleShortcuts} from '#/main/community/tools/community/role/containers/shortcuts'
import {RoleRights} from '#/main/community/tools/community/role/components/rights'

const RoleShow = (props) =>
  <RolePage
    path={props.path}
    role={props.role}
    reload={(role) => props.reload(role, props.contextData)}
  >
    {get(props.role, 'id') &&
      <RoleMetrics
        load={(year) => props.loadMetrics(props.role.id, year)}
      />
    }

    <DetailsData
      name={selectors.FORM_NAME}
      definition={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'meta.description',
              type: 'string',
              label: trans('description'),
              hideLabel: true,
              displayed: (role) => get(role, 'meta.description'),
              options: {
                long: true
              }
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

    <ContentSections level={3} defaultOpened="role-users">
      {'ROLE_ANONYMOUS' !== props.role.name &&
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
              displayed: (hasPermission('edit', props.role) && ('workspace' !== props.contextType || constants.ROLE_PLATFORM !== props.role.type)),
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
            path={props.path}
            name={`${selectors.FORM_NAME}.users`}
            url={['apiv2_role_list_users', {id: props.role.id}]}
            autoload={!!props.role.id}
            delete={{
              url: ['apiv2_role_remove_users', {id: props.role.id}],
              displayed: () => (hasPermission('edit', props.role) && ('workspace' !== props.contextType || constants.ROLE_PLATFORM !== props.role.type))
            }}
            actions={undefined}
          />
        </ContentSection>
      }

      {'ROLE_ANONYMOUS' !== props.role.name &&
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
              displayed: (hasPermission('edit', props.role) && ('workspace' !== props.contextType || constants.ROLE_PLATFORM !== props.role.type)),
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
            path={props.path}
            name={`${selectors.FORM_NAME}.groups`}
            url={['apiv2_role_list_groups', {id: props.role.id}]}
            autoload={!!props.role.id}
            delete={{
              url: ['apiv2_role_remove_groups', {id: props.role.id}],
              displayed: () => (hasPermission('edit', props.role) && ('workspace' !== props.contextType || constants.ROLE_PLATFORM !== props.role.type))
            }}
            actions={undefined}
          />
        </ContentSection>
      }

      {'ROLE_ANONYMOUS' !== props.role.name &&
        <br id="role-spacer"/>
      }

      {'workspace' === props.contextType &&
        <RoleShortcuts
          id="role-shortcuts"
          disabled={!props.role.id}
          role={props.role}
          workspace={props.contextData}
        />
      }

      {props.role.id && ('workspace' === props.contextType || constants.ROLE_WORKSPACE === props.role.type) &&
        <RoleRights
          id="role-workspace-rights"
          disabled={!props.role.id}
          icon="fa fa-fw fa-book"
          title={trans('workspace_tools')}
          role={props.role}
          contextType="workspace"
          contextId={props.contextData ? props.contextData.id : get(props.role, 'workspace.id')}
          rights={props.workspaceRights}
          reload={props.loadWorkspaceRights}
        />
      }

      {props.role.id && ('desktop' === props.contextType && constants.ROLE_WORKSPACE !== props.role.type) &&
        <RoleRights
          id="role-desktop-rights"
          disabled={!props.role.id}
          icon="fa fa-fw fa-tools"
          title={trans('desktop_tools')}
          role={props.role}
          contextType="desktop"
          rights={props.desktopRights}
          reload={props.loadDesktopRights}
        />
      }

      {props.role.id && ('desktop' === props.contextType && constants.ROLE_WORKSPACE !== props.role.type) &&
        <RoleRights
          id="role-administration-rights"
          disabled={!props.role.id}
          icon="fa fa-fw fa-cogs"
          title={trans('administration_tools')}
          role={props.role}
          contextType="administration"
          rights={props.administrationRights}
          reload={props.loadAdministrationRights}
        />
      }
    </ContentSections>
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
  loadMetrics: T.func.isRequired,
  loadWorkspaceRights: T.func.isRequired,
  loadDesktopRights: T.func.isRequired,
  loadAdministrationRights: T.func.isRequired,
  addUsers: T.func.isRequired,
  addGroups: T.func.isRequired
}

export {
  RoleShow
}
