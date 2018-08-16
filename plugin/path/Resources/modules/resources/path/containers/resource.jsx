import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {hasPermission} from '#/main/core/resource/permissions'

import {PathResource as PathResourceComponent} from '#/plugin/path/resources/path/components/resource'
import {reducer, selectors} from '#/plugin/path/resources/path/store'

const PathResource = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      overview: selectors.showOverview(state),
      editable: hasPermission('edit', resourceSelectors.resourceNode(state))
    })
  )(PathResourceComponent)
)

export {
  PathResource
}
