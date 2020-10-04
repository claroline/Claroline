import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'

import {FormSections, FormSection} from '#/main/app/content/form/components/sections'
import {FormData} from '#/main/app/content/form/containers/data'
import {ListData} from '#/main/app/content/list/containers/data'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {selectors as baseSelectors} from '#/main/core/administration/community/store'
import {actions} from '#/main/core/administration/community/location/store'
import {OrganizationList} from '#/main/core/administration/community/organization/components/organization-list'
import {UserList} from '#/main/core/administration/community/user/components/user-list'
import {GroupList} from '#/main/core/administration/community/group/components/group-list'

import {MODAL_USERS} from '#/main/core/modals/users'
import {MODAL_GROUPS} from '#/main/core/modals/groups'
import {MODAL_ORGANIZATIONS} from '#/main/core/modals/organizations'

import {locationTypes} from '#/main/core/administration/community/location/constants'

const LocationForm = props =>
  <FormData
    level={3}
    name={`${baseSelectors.STORE_NAME}.locations.current`}
    buttons={true}
    target={(location, isNew) => isNew ?
      ['apiv2_location_create'] :
      ['apiv2_location_update', {id: location.id}]
    }
    cancel={{
      type: LINK_BUTTON,
      target: props.path+'/locations',
      exact: true
    }}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'name',
            type: 'string',
            label: trans('name'),
            required: true
          }
        ]
      }, {
        title: trans('information'),
        icon: 'fa fa-fw fa-info',
        fields: [
          {
            name: 'meta.type',
            type: 'choice',
            label: trans('type'),
            options: {
              condensed: true,
              choices: locationTypes
            }
          }, {
            name: 'meta.description',
            type: 'html',
            label: trans('description')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        fields: [
          {
            name: 'poster',
            type: 'image',
            label: trans('poster')
          }, {
            name: 'thumbnail',
            type: 'image',
            label: trans('thumbnail')
          }
        ]
      }, {
        title: trans('contact_information'),
        icon: 'fa fa-fw fa-id-card',
        fields: [
          {
            name: 'phone',
            type: 'string',
            label: trans('phone')
          }, {
            name: 'address',
            type: 'address',
            label: trans('address')
          }
        ]
      }, {
        title: trans('geolocation'),
        icon: 'fa fa-fw fa-map-marker',
        fields: [
          {name: 'gps.latitude', type: 'number', label: trans('latitude')}, // todo make a field
          {name: 'gps.longitude', type: 'number', label: trans('longitude')}
        ]
      }
    ]}
  >
    <FormSections
      level={3}
    >
      <FormSection
        className="embedded-list-section"
        icon="fa fa-fw fa-user"
        title={trans('users')}
        disabled={props.new}
        actions={[
          {
            name: 'add-users',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_user'),
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
          name={`${baseSelectors.STORE_NAME}.locations.current.users`}
          fetch={{
            url: ['apiv2_location_list_users', {id: props.location.id}],
            autoload: props.location.id && !props.new
          }}
          primaryAction={(row) => ({
            type: LINK_BUTTON,
            target: `${props.path}/users/form/${row.id}`,
            label: trans('edit', {}, 'actions')
          })}
          delete={{
            url: ['apiv2_location_remove_users', {id: props.location.id}]
          }}
          definition={UserList.definition}
          card={UserList.card}
        />
      </FormSection>

      <FormSection
        className="embedded-list-section"
        icon="fa fa-fw fa-users"
        title={trans('groups')}
        disabled={props.new}
        actions={[
          {
            name: 'add-groups',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_group'),
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
          name={`${baseSelectors.STORE_NAME}.locations.current.groups`}
          fetch={{
            url: ['apiv2_location_list_groups', {id: props.location.id}],
            autoload: props.location.id && !props.new
          }}
          primaryAction={(row) => ({
            type: LINK_BUTTON,
            target: `${props.path}/groups/form/${row.id}`,
            label: trans('edit', {}, 'actions')
          })}
          delete={{
            url: ['apiv2_location_remove_groups', {id: props.location.id}]
          }}
          definition={GroupList.definition}
          card={GroupList.card}
        />
      </FormSection>

      <FormSection
        className="embedded-list-section"
        icon="fa fa-fw fa-building"
        title={trans('organizations')}
        disabled={props.new}
        actions={[
          {
            name: 'add-organizations',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_organizations'),
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
          name={`${baseSelectors.STORE_NAME}.locations.current.organizations`}
          fetch={{
            url: ['apiv2_location_list_organizations', {id: props.location.id}],
            autoload: props.location.id && !props.new
          }}
          primaryAction={(row) => ({
            type: LINK_BUTTON,
            target: `${props.path}/organizations/form/${row.id}`,
            label: trans('edit', {}, 'actions')
          })}
          delete={{
            url: ['apiv2_location_remove_organizations', {id: props.location.id}]
          }}
          definition={OrganizationList.definition}
          card={OrganizationList.card}
        />
      </FormSection>
    </FormSections>
  </FormData>

LocationForm.propTypes = {
  path: T.string.isRequired,
  new: T.bool.isRequired,
  location: T.shape({
    id: T.string
  }).isRequired,
  addUsers: T.func.isRequired,
  addGroups: T.func.isRequired,
  addOrganizations: T.func.isRequired
}

const Location = connect(
  state => ({
    path: toolSelectors.path(state),
    new: formSelect.isNew(formSelect.form(state, baseSelectors.STORE_NAME+'.locations.current')),
    location: formSelect.data(formSelect.form(state, baseSelectors.STORE_NAME+'.locations.current'))
  }),
  dispatch =>({
    addUsers(locationId, selected) {
      dispatch(actions.addUsers(locationId, selected.map(row => row.id)))
    },
    addGroups(locationId, selected) {
      dispatch(actions.addGroups(locationId, selected.map(row => row.id)))
    },
    addOrganizations(locationId, selected) {
      dispatch(actions.addOrganizations(locationId, selected.map(row => row.id)))
    }
  })
)(LocationForm)

export {
  Location
}
