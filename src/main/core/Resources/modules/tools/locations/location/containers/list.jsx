import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {actions} from '#/main/core/tools/locations/location/store'
import {LocationList as LocationListComponent} from '#/main/core/tools/locations/location/components/list'

const LocationList = connect(
  (state) => ({
    path: toolSelectors.path(state)
  }),
  (dispatch) => ({
    geolocate: (location) => dispatch(actions.geolocate(location))
  })
)(LocationListComponent)

export {
  LocationList
}
