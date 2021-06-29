import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {LocationDetails as LocationDetailsComponent} from '#/main/core/tools/locations/location/components/details'
import {selectors} from '#/main/core/tools/locations/location/store'

const LocationDetails = connect(
  (state) => ({
    path: toolSelectors.path(state),
    location: selectors.currentLocation(state)
  })
)(LocationDetailsComponent)

export {
  LocationDetails
}
