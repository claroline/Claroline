import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'

import {PathResource as PathResourceComponent} from '#/plugin/path/resources/path/components/resource'
import {reducer, selectors} from '#/plugin/path/resources/path/store'

const PathResource = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      overview: selectors.showOverview(state)
    })
  )(PathResourceComponent)
)

export {
  PathResource
}
