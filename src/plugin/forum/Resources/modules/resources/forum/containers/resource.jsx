import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as securitySelectors} from '#/main/app/security/store'

import {ForumResource as ForumResourceComponent} from '#/plugin/forum/resources/forum/components/resource'
import {actions, reducer, selectors} from '#/plugin/forum/resources/forum/store'

const ForumResource = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      currentUser: securitySelectors.currentUser(state),
      forum: selectors.forum(state),
      moderator: selectors.moderator(state)
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
