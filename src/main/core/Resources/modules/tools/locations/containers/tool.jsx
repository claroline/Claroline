import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {LocationsTool as LocationsToolComponent} from '#/main/core/tools/locations/components/tool'

const LocationsTool = connect(
  (state) => ({
    path: toolSelectors.path(state)
  })
)(LocationsToolComponent)

export {
  LocationsTool
}
