import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'
import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool/store/selectors'

import {TagsTool as TagsToolComponent} from '#/plugin/tag/tools/tags/components/tool'
import {actions, reducer, selectors} from '#/plugin/tag/tools/tags/store'

const TagsTool = withReducer(selectors.STORE_NAME, reducer)(
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
