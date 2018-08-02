import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {t} from '#/main/core/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {ListData} from '#/main/app/content/list/containers/data'
import {actions} from '#/main/core/administration/user/location/actions'
import {LocationList} from '#/main/core/administration/user/location/components/location-list'

/**
 * Locations list.
 *
 * @param props
 * @constructor
 */
const LocationsList = props =>
  <ListData
    name="locations.list"
    fetch={{
      url: ['apiv2_location_list'],
      autoload: true
    }}
    definition={LocationList.definition}
    primaryAction={LocationList.open}
    delete={{
      url: ['apiv2_location_delete_bulk']
    }}
    actions={(rows) => [{
      type: CALLBACK_BUTTON,
      icon: 'fa fa-fw fa-map-marker',
      label: t('geolocate'),
      callback: () => props.geolocate(rows[0]),
      scope: ['object'] // todo should be available in selection mode too
    }]}
    card={LocationList.card}
  />

LocationsList.propTypes = {
  geolocate: T.func.isRequired
}

const Locations = connect(
  null,
  (dispatch) => ({
    geolocate: (location) => dispatch(actions.geolocate(location))
  })
)(LocationsList)

export {
  Locations
}
