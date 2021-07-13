import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {LocationNew as LocationNewComponent} from '#/main/core/tools/locations/location/components/new'

const LocationNew = connect(
  (state) => ({
    path: toolSelectors.path(state)
  })
)(LocationNewComponent)

export {
  LocationNew
}
