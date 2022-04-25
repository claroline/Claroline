import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store/selectors'

import {TagsTool as TagsToolComponent} from '#/plugin/tag/tools/tags/components/tool'
import {actions} from '#/plugin/tag/tools/tags/store'

const TagsTool = withRouter(
  connect(
    (state) => ({
      canCreate: hasPermission('create', toolSelectors.toolData(state))
    }),
    (dispatch) => ({
      openForm(tagId = null) {
        dispatch(actions.openForm(tagId))
      }
    })
  )(TagsToolComponent)
)

export {
  TagsTool
}
