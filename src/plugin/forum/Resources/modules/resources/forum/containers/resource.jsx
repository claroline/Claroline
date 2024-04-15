import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as resourceSelectors} from '#/main/core/resource/store'
import {hasPermission} from '#/main/app/security'

import {ForumResource as ForumResourceComponent} from '#/plugin/forum/resources/forum/components/resource'
import {actions, reducer, selectors} from '#/plugin/forum/resources/forum/store'

const ForumResource = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      currentUser: securitySelectors.currentUser(state),
      overview: selectors.overview(state),
      forum: selectors.forum(state),
      moderator: selectors.moderator(state),
      editable: hasPermission('edit', resourceSelectors.resourceNode(state))
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
