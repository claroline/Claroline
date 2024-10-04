import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {DetailsData} from '#/main/app/content/details/containers/data'
import {Alert} from '#/main/app/components/alert'
import {PageSection} from '#/main/app/page/components/section'
import {PageTabbedSection} from '#/main/app/page/components/tabbed-section'
import {ContentHtml} from '#/main/app/content/components/html'

import {MODAL_USERS} from '#/main/community/modals/users'
import {UserList} from '#/main/community/user/components/list'
import {MODAL_ROLES} from '#/main/community/modals/roles'
import {RoleList} from '#/main/community/role/components/list'

import {Group as GroupTypes} from '#/main/community/group/prop-types'
import {selectors} from '#/main/community/tools/community/group/store/selectors'
import {GroupPage} from '#/main/community/group/components/page'
import {MODAL_ORGANIZATIONS} from '#/main/community/modals/organizations'
import {OrganizationList} from '#/main/community/organization/components/list'
import {route} from '#/main/community/group/routing'

const GroupShow = (props) =>
  <GroupPage
    path={props.path}
    group={props.group}
    reload={props.reload}
  >
    {get(props.group, 'meta.description') &&
      <PageSection size="md" className="pb-5">
        <ContentHtml className="lead">{get(props.group, 'meta.description')}</ContentHtml>
      </PageSection>
    }

    <PageSection size="md" className="bg-body-tertiary">
      {hasPermission('administrate', props.group) && get(props.group, 'meta.readOnly') &&
        <Alert type="info" className="my-3">{trans('group_locked', {}, 'community')}</Alert>
      }

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
              }
            ]
          }
        ]}
      />
    </PageSection>

    <PageTabbedSection
      size="md"
      className="py-3 embedded-list-section"
      path={route(props.group, props.path)}
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
              url={['apiv2_group_list_users', {id: props.group.id}]}
              addAction={{
                name: 'add-users',
                type: MODAL_BUTTON,
                icon: 'fa fa-fw fa-plus',
                tooltip: 'bottom',
                label: trans('add_users'),
                disabled: get(props.group, 'meta.readOnly'),
                displayed: hasPermission('administrate', props.group),
                modal: [MODAL_USERS, {
                  selectAction: (selected) => ({
                    type: CALLBACK_BUTTON,
                    label: trans('add', {}, 'actions'),
                    callback: () => props.addUsers(props.group.id, selected)
                  })
                }]
              }}
              autoload={!!props.group.id}
              delete={{
                url: ['apiv2_group_remove_users', {id: props.group.id}],
                label: trans('unregister', {}, 'actions'),
                disabled: () => get(props.group, 'meta.readOnly'),
                displayed: () => hasPermission('administrate', props.group)
              }}
              actions={undefined}
            />
          )
        }, {
          path: '/organizations',
          icon: 'fa fa-building',
          title: trans('organizations', {}, 'community'),
          render: () => (
            <OrganizationList
              className="mt-3"
              path={props.path}
              name={`${selectors.FORM_NAME}.organizations`}
              url={['apiv2_group_list_organizations', {id: props.group.id}]}
              autoload={!!props.group.id}
              addAction={{
                name: 'add',
                type: MODAL_BUTTON,
                icon: 'fa fa-fw fa-plus',
                tooltip: 'bottom',
                label: trans('add_organizations'),
                displayed: hasPermission('administrate', props.group),
                modal: [MODAL_ORGANIZATIONS, {
                  selectAction: (organizations) => ({
                    type: CALLBACK_BUTTON,
                    label: trans('add', {}, 'actions'),
                    callback: () => props.addOrganizations(props.group.id, organizations)
                  })
                }]
              }}
              delete={{
                url: ['apiv2_group_remove_organizations', {id: props.group.id}],
                displayed: () => hasPermission('administrate', props.group)
              }}
              actions={undefined}
            />
          )
        }, {
          path: '/roles',
          icon: 'fa fa-id-badge',
          title: trans('roles', {}, 'community'),
          displayed: hasPermission('administrate', props.group),
          render: () => (
            <RoleList
              className="mt-3"
              path={props.path}
              name={`${selectors.FORM_NAME}.roles`}
              url={['apiv2_group_list_roles', {id: props.group.id}]}
              autoload={!!props.group.id}
              addAction={{
                name: 'add-roles',
                type: MODAL_BUTTON,
                icon: 'fa fa-fw fa-plus',
                tooltip: 'bottom',
                label: trans('add_roles'),
                disabled: get(props.group, 'meta.readOnly'),
                modal: [MODAL_ROLES, {
                  selectAction: (selected) => ({
                    type: CALLBACK_BUTTON,
                    label: trans('add', {}, 'actions'),
                    callback: () => props.addRoles(props.group.id, selected)
                  })
                }]
              }}
              delete={{
                url: ['apiv2_group_remove_roles', {id: props.group.id}],
                disabled: () => get(props.group, 'meta.readOnly')
              }}
              actions={undefined}
            />
          )
        }
      ]}
    />
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
