import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'

import {Location as LocationTypes} from '#/main/core/tools/locations/prop-types'
import {LocationPage} from '#/main/core/tools/locations//containers/page'
import {LocationAbout} from '#/main/core/tools/locations//components/about'
import {LocationForm} from '#/main/core/tools/locations//containers/form'

const LocationDetails = (props) =>
  <LocationPage
    location={props.location}
  >
    <Routes
      path={props.path + '/' + props.location.id}
      routes={[
        {
          path: '/',
          exact: true,
          render: () => (
            <LocationAbout
              location={props.location}
              addOrganizations={props.addOrganizations}
            />
          )
        }, {
          path: '/edit',
          component: LocationForm
        }
      ]}
    />
  </LocationPage>

LocationDetails.propTypes = {
  path: T.string.isRequired,
  location: T.shape(
    LocationTypes.propTypes
  ),
  addUsers: T.func.isRequired,
  addGroups: T.func.isRequired,
  addOrganizations: T.func.isRequired
}

export {
  LocationDetails
}
