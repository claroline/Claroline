import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {hasPermission} from '#/main/app/security'

import {PathResource as PathResourceComponent} from '#/plugin/path/resources/path/components/resource'
import {selectors} from '#/plugin/path/resources/path/store'

const PathResource = withRouter(
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
