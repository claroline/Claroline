import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {User as UserTypes} from '#/main/community/user/prop-types'
import {UserPage} from '#/main/community/user/components/page'
import {ProfileShow} from '#/main/community/profile/containers/show'

import {selectors} from '#/main/community/tools/community/user/store'
import {hasPermission} from '#/main/app/security'
import {ContentSection, ContentSections} from '#/main/app/content/components/sections'
import {trans} from '#/main/app/intl'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_ROLES} from '#/main/community/modals/roles'
import {RoleList} from '#/main/community/role/components/list'
import {MODAL_GROUPS} from '#/main/community/modals/groups'
import {GroupList} from '#/main/community/group/components/list'
import {MODAL_ORGANIZATIONS} from '#/main/community/modals/organizations'
import {OrganizationList} from '#/main/community/organization/components/list'

const UserShow = (props) =>
  <UserPage
    path={props.path}
    user={props.user}
    reload={props.reload}
  >
    {!isEmpty(props.user) &&
      <ProfileShow
        path={`${props.path}/users/${props.user.username}`}
        name={selectors.FORM_NAME}
        user={props.user}
      >
        <ContentSections level={3}>
          <ContentSection
            id="user-groups"
            icon="fa fa-fw fa-users"
            className="embedded-list-section"
            title={trans('groups', {}, 'community')}
            disabled={!props.user.id}
            actions={[
              {
                name: 'add',
                type: MODAL_BUTTON,
                icon: 'fa fa-fw fa-plus',
                label: trans('add_group'),
                displayed: hasPermission('administrate', props.user),
                modal: [MODAL_GROUPS, {
                  selectAction: (groups) => ({
                    type: CALLBACK_BUTTON,
                    label: trans('add', {}, 'actions'),
                    callback: () => props.addGroups(props.user.id, groups.map(group => group.id))
                  })
                }]
              }
            ]}
          >
            <GroupList
              path={props.path}
              name={`${selectors.FORM_NAME}.groups`}
              url={['apiv2_user_list_groups', {id: props.user.id}]}
              autoload={!!props.user.id}
              delete={{
                url: ['apiv2_user_remove_groups', {id: props.user.id}],
                displayed: () => hasPermission('administrate', props.user)
              }}
              actions={undefined}
            />
          </ContentSection>

          <ContentSection
            id="user-organizations"
            icon="fa fa-fw fa-building"
            className="embedded-list-section"
            title={trans('organizations', {}, 'community')}
            disabled={!props.user.id}
            actions={[
              {
                name: 'add',
                type: MODAL_BUTTON,
                icon: 'fa fa-fw fa-plus',
                label: trans('add_organization'),
                displayed: hasPermission('administrate', props.user),
                modal: [MODAL_ORGANIZATIONS, {
                  selectAction: (organizations) => ({
                    type: CALLBACK_BUTTON,
                    label: trans('add', {}, 'actions'),
                    callback: () => props.addOrganizations(props.user.id, organizations.map(organization => organization.id))
                  })
                }]
              }
            ]}
          >
            <OrganizationList
              path={props.path}
              name={`${selectors.FORM_NAME}.organizations`}
              url={['apiv2_user_list_organizations', {id: props.user.id}]}
              autoload={!!props.user.id}
              delete={{
                url: ['apiv2_user_remove_organizations', {id: props.user.id}],
                displayed: () => hasPermission('administrate', props.user)
              }}
              actions={() => []}
            />
          </ContentSection>

          {hasPermission('administrate', props.user) &&
            <ContentSection
              id="user-roles"
              className="embedded-list-section"
              icon="fa fa-fw fa-id-badge"
              title={trans('roles', {}, 'community')}
              disabled={!props.user.id}
              actions={[
                {
                  name: 'add-roles',
                  type: MODAL_BUTTON,
                  icon: 'fa fa-fw fa-plus',
                  label: trans('add_roles'),
                  modal: [MODAL_ROLES, {
                    selectAction: (selected) => ({
                      type: CALLBACK_BUTTON,
                      label: trans('add', {}, 'actions'),
                      callback: () => props.addRoles(props.user.id, selected.map(role => role.id))
                    })
                  }]
                }
              ]}
            >
              <RoleList
                path={props.path}
                name={`${selectors.FORM_NAME}.roles`}
                url={['apiv2_user_list_roles', {id: props.user.id}]}
                autoload={!!props.user.id}
                delete={{
                  url: ['apiv2_user_remove_roles', {id: props.user.id}]
                }}
                actions={undefined}
              />
            </ContentSection>
          }
        </ContentSections>
      </ProfileShow>
    }
  </UserPage>

UserShow.propTypes = {
  path: T.string.isRequired,
  user: T.shape(
    UserTypes.propTypes
  ),
  reload: T.func.isRequired,
  addRoles: T.func.isRequired,
  addOrganizations: T.func.isRequired,
  addGroups: T.func.isRequired
}

export {
  UserShow
}
