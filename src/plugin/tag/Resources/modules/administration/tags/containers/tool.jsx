import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {TagsTool as TagsToolComponent} from '#/plugin/tag/administration/tags/components/tool'
import {actions} from '#/plugin/tag/administration/tags/store'

const TagsTool = withRouter(
  connect(
    null,
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
