import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import {actions, selectors} from '#/plugin/open-badge/tools/badges/store'
import {BadgeShow as BadgeShowComponent} from '#/plugin/open-badge/badge/components/show'

const BadgeShow = connect(
  state => ({
    path: toolSelectors.path(state),
    contextType: toolSelectors.contextType(state),
    contextId: toolSelectors.contextId(state),
    badge: selectors.currentBadge(state),
    assertion: selectors.assertion(state),
    evidences: selectors.evidences(state)
  }),
  dispatch =>({
    reload(id) {
      dispatch(actions.openBadge(id))
    }
  })
)(BadgeShowComponent)

export {
  BadgeShow
}
