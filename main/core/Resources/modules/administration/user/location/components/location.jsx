import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {t} from '#/main/core/translation'

import {FormSections, FormSection} from '#/main/core/layout/form/components/form-sections.jsx'
import {FormContainer} from '#/main/core/data/form/containers/form.jsx'
import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'
import {select as formSelect} from '#/main/core/data/form/selectors'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {MODAL_DATA_PICKER} from '#/main/core/data/list/modals'
import {actions} from '#/main/core/administration/user/location/actions'
import {OrganizationList} from '#/main/core/administration/user/organization/components/organization-list.jsx'
import {UserList} from '#/main/core/administration/user/user/components/user-list.jsx'
import {GroupList} from '#/main/core/administration/user/group/components/group-list.jsx'

import {locationTypes} from '#/main/core/administration/user/location/constants'

const LocationForm = props =>
  <FormContainer
    level={3}
    name="locations.current"
    sections={[
      {
        id: 'general',
        title: t('general'),
        primary: true,
        fields: [
          {name: 'name', type: 'string', label: t('name'), required: true},
          {name: 'meta.type', type: 'enum', label: t('type'), options: {choices: locationTypes}}
        ]
      }, {
        id: 'contact',
        title: t('contact'),
        icon: 'fa fa-fw fa-address-card',
        fields: [
          {name: 'phone', type: 'text', label: t('phone')},
          {name: 'street', type: 'string', label: t('street'), required: true},
          {name: 'boxNumber', type: 'string', label: t('box_number')},
          {name: 'streetNumber', type: 'string', label: t('street_number'), required: true},
          {name: 'zipCode', type: 'string', label: t('postal_code'), required: true},
          {name: 'town', type: 'string', label: t('town'), required: true},
          {name: 'country', type: 'string', label: t('country'), required: true}
        ]
      }, {
        id: 'geolocation',
        title: t('geolocation'),
        icon: 'fa fa-fw fa-map-marker',
        fields: [
          {name: 'gps.latitude', type: 'number', label: t('latitude')}, // todo make a field
          {name: 'gps.longitude', type: 'number', label: t('longitude')}
        ]
      }
    ]}
  >
    <FormSections
      level={3}
    >
      <FormSection
        id="location-users"
        icon="fa fa-fw fa-user"
        title={t('users')}
        disabled={props.new}
        actions={[
          {
            icon: 'fa fa-fw fa-plus',
            label: t('add_user'),
            action: () => props.pickUsers(props.location.id)
          }
        ]}
      >
        <DataListContainer
          name="locations.current.users"
          open={UserList.open}
          fetch={{
            url: ['apiv2_location_list_users', {id: props.location.id}],
            autoload: props.location.id && !props.new
          }}
          delete={{
            url: ['apiv2_location_remove_users', {id: props.location.id}]
          }}
          definition={UserList.definition}
          card={UserList.card}
        />
      </FormSection>

      <FormSection
        id="location-groups"
        icon="fa fa-fw fa-users"
        title={t('groups')}
        disabled={props.new}
        actions={[
          {
            icon: 'fa fa-fw fa-plus',
            label: t('add_group'),
            action: () => props.pickGroups(props.location.id)
          }
        ]}
      >
        <DataListContainer
          name="locations.current.groups"
          open={GroupList.open}
          fetch={{
            url: ['apiv2_location_list_groups', {id: props.location.id}],
            autoload: props.location.id && !props.new
          }}
          delete={{
            url: ['apiv2_location_remove_groups', {id: props.location.id}]
          }}
          definition={GroupList.definition}
          card={GroupList.card}
        />
      </FormSection>

      <FormSection
        id="location-organizations"
        icon="fa fa-fw fa-building"
        title={t('organizations')}
        disabled={props.new}
        actions={[
          {
            icon: 'fa fa-fw fa-plus',
            label: t('add_organizations'),
            action: () => props.pickOrganizations(props.location.id)
          }
        ]}
      >
        <DataListContainer
          name="locations.current.organizations"
          open={OrganizationList.open}
          fetch={{
            url: ['apiv2_location_list_organizations', {id: props.location.id}],
            autoload: props.location.id && !props.new
          }}
          delete={{
            url: ['apiv2_location_remove_organizations', {id: props.location.id}]
          }}
          definition={OrganizationList.definition}
          card={OrganizationList.card}
        />
      </FormSection>
    </FormSections>
  </FormContainer>

LocationForm.propTypes = {
  new: T.bool.isRequired,
  location: T.shape({
    id: T.string
  }).isRequired,
  pickUsers: T.func.isRequired,
  pickGroups: T.func.isRequired,
  pickOrganizations: T.func.isRequired
}

const Location = connect(
  state => ({
    new: formSelect.isNew(formSelect.form(state, 'locations.current')),
    location: formSelect.data(formSelect.form(state, 'locations.current'))
  }),
  dispatch =>({
    pickUsers(locationId) {
      dispatch(modalActions.showModal(MODAL_DATA_PICKER, {
        icon: 'fa fa-fw fa-user',
        title: t('add_users'),
        confirmText: t('add'),
        name: 'users.picker',
        definition: UserList.definition,
        card: UserList.card,
        fetch: {
          url: ['apiv2_user_list'],
          autoload: true
        },
        handleSelect: (selected) => dispatch(actions.addUsers(locationId, selected))
      }))
    },
    pickGroups(locationId) {
      dispatch(modalActions.showModal(MODAL_DATA_PICKER, {
        icon: 'fa fa-fw fa-users',
        title: t('add_groups'),
        confirmText: t('add'),
        name: 'groups.picker',
        definition: GroupList.definition,
        card: GroupList.card,
        fetch: {
          url: ['apiv2_group_list'],
          autoload: true
        },
        handleSelect: (selected) => dispatch(actions.addGroups(locationId, selected))
      }))
    },
    pickOrganizations(locationId) {
      dispatch(modalActions.showModal(MODAL_DATA_PICKER, {
        icon: 'fa fa-fw fa-buildings',
        title: t('add_organizations'),
        confirmText: t('add'),
        name: 'organizations.picker',
        definition: OrganizationList.definition,
        card: OrganizationList.card,
        fetch: {
          url: ['apiv2_organization_list'],
          autoload: true
        },
        handleSelect: (selected) => dispatch(actions.addOrganizations(locationId, selected))
      }))
    }
  })
)(LocationForm)

export {
  Location
}
