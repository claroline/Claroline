import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {selectors as resourceSelect} from '#/main/core/resource/store/selectors'
import {hasPermission} from '#/main/core/resource/permissions'

import {ForumResource as ForumResourceComponent} from '#/plugin/forum/resources/forum/components/resource'
import {actions, reducer, select} from '#/plugin/forum/resources/forum/store'

const ForumResource = withReducer(select.STORE_NAME, reducer)(
  connect(
    (state) => ({
      forum: select.forum(state),
      editable: hasPermission('edit', resourceSelect.resourceNode(state))
    }),
    (dispatch) => ({
      loadLastMessages(forum) {
        dispatch(actions.fetchLastMessages(forum))
      },
      notify(forum, user) {
        dispatch(actions.notify(forum, user))
      },
      stopNotify(forum, user) {
        dispatch(actions.stopNotify(forum, user))
      }
    })
  )(ForumResourceComponent)
)

export {
  ForumResource
}