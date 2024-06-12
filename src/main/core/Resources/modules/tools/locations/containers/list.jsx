import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {LocationList as LocationListComponent} from '#/main/core/tools/locations//components/list'

const LocationList = connect(
  (state) => ({
    path: toolSelectors.path(state)
  })
)(LocationListComponent)

export {
  LocationList
}
