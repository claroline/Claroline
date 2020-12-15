import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors as baseSelectors} from '#/main/core/administration/community/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {actions} from '#/main/core/administration/community/location/store'
import {LocationList} from '#/main/core/administration/community/location/components/location-list'

/**
 * Locations list.
 *
 * @param props
 * @constructor
 */
const LocationsList = props =>
  <ListData
    name={`${baseSelectors.STORE_NAME}.locations.list`}
    fetch={{
      url: ['apiv2_location_list'],
      autoload: true
    }}
    definition={LocationList.definition}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: `${props.path}/locations/form/${row.id}`,
      label: trans('edit', {}, 'actions')
    })}
    delete={{
      url: ['apiv2_location_delete_bulk']
    }}
    actions={(rows) => [{
      type: CALLBACK_BUTTON,
      icon: 'fa fa-fw fa-map-marker',
      label: trans('geolocate'),
      callback: () => props.geolocate(rows[0]),
      scope: ['object'] // todo should be available in selection mode too
    }]}
    card={LocationList.card}
  />

LocationsList.propTypes = {
  path: T.string.isRequired,
  geolocate: T.func.isRequired
}

const Locations = connect(
  (state) => ({
    path: toolSelectors.path(state)
  }),
  (dispatch) => ({
    geolocate: (location) => dispatch(actions.geolocate(location))
  })
)(LocationsList)

export {
  Locations
}
