import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {LocationDetails as LocationDetailsComponent} from '#/main/core/tools/locations//components/details'
import {selectors} from '#/main/core/tools/locations//store'

const LocationDetails = connect(
  (state) => ({
    path: toolSelectors.path(state),
    location: selectors.currentLocation(state)
  })
)(LocationDetailsComponent)

export {
  LocationDetails
}
