import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {LocationsTool as LocationsToolComponent} from '#/main/core/tools/locations/components/tool'
import {reducer, selectors} from '#/main/core/tools/locations/store'

const LocationsTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      path: toolSelectors.path(state)
    })
  )(LocationsToolComponent)
)

export {
  LocationsTool
}
