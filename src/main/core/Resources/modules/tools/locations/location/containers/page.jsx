import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {LocationPage as LocationPageComponent} from '#/main/core/tools/locations/location/components/page'

const LocationPage = connect(
  (state) => ({
    path: toolSelectors.path(state),
    currentContext: toolSelectors.context(state)
  })
)(LocationPageComponent)

export {
  LocationPage
}
