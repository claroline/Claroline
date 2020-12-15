import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {hasPermission} from '#/main/app/security'

import {PathMenu as PathMenuComponent} from '#/plugin/path/resources/path/components/menu'

const PathMenu = withRouter(
  connect(
    (state) => ({
      editable: hasPermission('edit', resourceSelectors.resourceNode(state))
    })
  )(PathMenuComponent)
)

export {
  PathMenu
}
