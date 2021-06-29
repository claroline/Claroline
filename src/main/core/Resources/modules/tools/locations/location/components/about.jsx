import React, {Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {hasPermission} from '#/main/app/security'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ContentSections, ContentSection} from '#/main/app/content/components/sections'
import {ListData} from '#/main/app/content/list/containers/data'
import {DetailsData} from '#/main/app/content/details/containers/data'
import {route as userRoute} from '#/main/core/user/routing'

import {Location as LocationTypes} from '#/main/core/tools/locations/prop-types'
import {selectors} from '#/main/core/tools/locations/location/store'

import {MODAL_USERS} from '#/main/core/modals/users'
import {UserList} from '#/main/core/administration/community/user/components/user-list'
import {MODAL_GROUPS} from '#/main/core/modals/groups'
import {GroupList} from '#/main/core/administration/community/group/components/group-list'
import {MODAL_ORGANIZATIONS} from '#/main/core/modals/organizations'
import {OrganizationList} from '#/main/core/administration/community/organization/components/organization-list'

const LocationAbout = (props) =>
  <Fragment>
    <DetailsData
      name={`${selectors.STORE_NAME}.current`}
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'meta.description',
              type: 'html',
              label: trans('description')
            }, {
              name: 'phone',
              type: 'string',
              label: trans('phone')
            }, {
              name: 'address',
              type: 'address',
              label: trans('address')
            }
          ]
        }
      ]}
    />

    <ContentSections
      level={3}
    >
      <ContentSection
        className="embedded-list-section"
        icon="fa fa-fw fa-user"
        title={trans('users')}
        actions={[
          {
            name: 'add-users',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_user'),
            displayed: hasPermission('edit', props.location),
            modal: [MODAL_USERS, {
              selectAction: (selected) => ({
                type: CALLBACK_BUTTON,
                label: trans('add', {}, 'actions'),
                callback: () => props.addUsers(props.location.id, selected)
              })
            }]
          }
        ]}
      >
        <ListData
          name={`${selectors.STORE_NAME}.current.users`}
          fetch={{
            url: ['apiv2_location_list_users', {id: props.location.id}],
            autoload: true
          }}
          primaryAction={(row) => ({
            type: LINK_BUTTON,
            target: userRoute(row)
          })}
          delete={{
            url: ['apiv2_location_remove_users', {id: props.location.id}],
            displayed: () => hasPermission('edit', props.location)
          }}
          definition={UserList.definition}
          card={UserList.card}
        />
      </ContentSection>

      <ContentSection
        className="embedded-list-section"
        icon="fa fa-fw fa-users"
        title={trans('groups')}
        actions={[
          {
            name: 'add-groups',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_group'),
            displayed: hasPermission('edit', props.location),
            modal: [MODAL_GROUPS, {
              url: ['apiv2_group_list'],
              selectAction: (selected) => ({
                type: CALLBACK_BUTTON,
                label: trans('add', {}, 'actions'),
                callback: () => props.addGroups(props.location.id, selected)
              })
            }]
          }
        ]}
      >
        <ListData
          name={`${selectors.STORE_NAME}.current.groups`}
          fetch={{
            url: ['apiv2_location_list_groups', {id: props.location.id}],
            autoload: true
          }}
          delete={{
            url: ['apiv2_location_remove_groups', {id: props.location.id}],
            displayed: () => hasPermission('edit', props.location)
          }}
          definition={GroupList.definition}
          card={GroupList.card}
        />
      </ContentSection>

      <ContentSection
        className="embedded-list-section"
        icon="fa fa-fw fa-building"
        title={trans('organizations')}
        actions={[
          {
            name: 'add-organizations',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_organizations'),
            displayed: hasPermission('edit', props.location),
            modal: [MODAL_ORGANIZATIONS, {
              selectAction: (selected) => ({
                type: CALLBACK_BUTTON,
                label: trans('add', {}, 'actions'),
                callback: () => props.addOrganizations(props.location.id, selected)
              })
            }]
          }
        ]}
      >
        <ListData
          name={`${selectors.STORE_NAME}.current.organizations`}
          fetch={{
            url: ['apiv2_location_list_organizations', {id: props.location.id}],
            autoload: true
          }}
          delete={{
            url: ['apiv2_location_remove_organizations', {id: props.location.id}],
            displayed: () => hasPermission('edit', props.location)
          }}
          definition={OrganizationList.definition}
          card={OrganizationList.card}
        />
      </ContentSection>
    </ContentSections>
  </Fragment>

LocationAbout.propTypes = {
  location: T.shape(
    LocationTypes.propTypes
  ),
  addUsers: T.func.isRequired,
  addGroups: T.func.isRequired,
  addOrganizations: T.func.isRequired
}

export {
  LocationAbout
}
