import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {t} from '#/main/core/translation'

import {PageActions, PageAction} from '#/main/core/layout/page/components/page-actions.jsx'
import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'

import {actions} from '#/main/core/administration/user/location/actions'
import {LocationList} from '#/main/core/administration/user/location/components/location-list.jsx'

/**
 * Locations list actions.
 *
 * @constructor
 */
const LocationsActions = () =>
  <PageActions>
    <PageAction
      id="location-add"
      icon="fa fa-plus"
      title={t('add_location')}
      action="#/locations/add"
      primary={true}
    />
  </PageActions>

/**
 * Locations list.
 *
 * @param props
 * @constructor
 */
const LocationsList = props =>
  <DataListContainer
    name="locations.list"
    open={LocationList.open}
    fetch={{
      url: ['apiv2_location_list'],
      autoload: true
    }}
    delete={{
      url: ['apiv2_location_delete_bulk']
    }}
    definition={LocationList.definition}
    actions={[{
      icon: 'fa fa-fw fa-map-marker',
      label: t('geolocate'),
      action: (rows) => props.geolocate(rows[0]),
      context: 'row' // todo should be available in selection mode too
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
  LocationsActions,
  Locations
}
