import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as resourceSelect} from '#/main/core/resource/store/selectors'
import {hasPermission} from '#/main/app/security'

import {ForumResource as ForumResourceComponent} from '#/plugin/forum/resources/forum/components/resource'
import {actions, reducer, select} from '#/plugin/forum/resources/forum/store'

const ForumResource = withRouter(
  withReducer(select.STORE_NAME, reducer)(
    connect(
      (state) => ({
        currentUser: securitySelectors.currentUser(state),
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
)

export {
  ForumResource
}
