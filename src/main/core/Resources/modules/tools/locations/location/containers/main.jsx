import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {LocationMain as LocationMainComponent} from '#/main/core/tools/locations/location/components/main'
import {actions} from '#/main/core/tools/locations/location/store'

const LocationMain = connect(
  (state) => ({
    path: toolSelectors.path(state)
  }),
  dispatch => ({
    open(id = null) {
      dispatch(actions.open(id))
    }
  })
)(LocationMainComponent)

export {
  LocationMain
}
