import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {ContentSection, ContentSections} from '#/main/app/content/components/sections'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {DetailsData} from '#/main/app/content/details/containers/data'
import {Alert} from '#/main/app/alert/components/alert'

import {MODAL_USERS} from '#/main/community/modals/users'
import {UserList} from '#/main/community/user/components/list'
import {MODAL_ROLES} from '#/main/community/modals/roles'
import {RoleList} from '#/main/community/role/components/list'

import {Group as GroupTypes} from '#/main/community/group/prop-types'
import {selectors} from '#/main/community/tools/community/group/store/selectors'
import {GroupPage} from '#/main/community/group/components/page'
import {MODAL_ORGANIZATIONS} from '#/main/community/modals/organizations'
import {OrganizationList} from '#/main/community/organization/components/list'

const GroupShow = (props) =>
  <GroupPage
    path={props.path}
    group={props.group}
    reload={props.reload}
  >
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
              displayed: (group) => get(group, 'meta.description'),
              options: {
                long: true
              }
            }
          ]
        }
      ]}
    />

    {hasPermission('edit', props.group) && get(props.group, 'meta.readOnly') &&
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
            displayed: hasPermission('edit', props.group),
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
          path={props.path}
          name={`${selectors.FORM_NAME}.users`}
          url={['apiv2_group_list_users', {id: props.group.id}]}
          autoload={!!props.group.id}
          delete={{
            url: ['apiv2_group_remove_users', {id: props.group.id}],
            label: trans('unregister', {}, 'actions'),
            disabled: () => get(props.group, 'meta.readOnly'),
            displayed: () => hasPermission('edit', props.group)
          }}
          actions={undefined}
        />
      </ContentSection>

      <ContentSection
        id="group-organizations"
        icon="fa fa-fw fa-building"
        className="embedded-list-section"
        title={trans('organizations', {}, 'community')}
        disabled={!props.group.id}
        actions={[
          {
            name: 'add',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_organization'),
            displayed: hasPermission('edit', props.group),
            modal: [MODAL_ORGANIZATIONS, {
              selectAction: (organizations) => ({
                type: CALLBACK_BUTTON,
                label: trans('add', {}, 'actions'),
                callback: () => props.addOrganizations(props.group.id, organizations)
              })
            }]
          }
        ]}
      >
        <OrganizationList
          path={props.path}
          name={`${selectors.FORM_NAME}.organizations`}
          url={['apiv2_group_list_organizations', {id: props.group.id}]}
          autoload={!!props.group.id}
          delete={{
            url: ['apiv2_group_remove_organizations', {id: props.group.id}],
            displayed: () => hasPermission('edit', props.group)
          }}
          actions={() => []}
        />
      </ContentSection>

      {hasPermission('edit', props.group) &&
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
            path={props.path}
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
  contextType: T.string.isRequired,
  group: T.shape(
    GroupTypes.propTypes
  ),
  reload: T.func.isRequired,
  addUsers: T.func.isRequired,
  addRoles: T.func.isRequired,
  addOrganizations: T.func.isRequired
}

export {
  GroupShow
}
