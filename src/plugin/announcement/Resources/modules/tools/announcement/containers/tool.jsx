import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'
import {actions as formActions} from '#/main/app/content/form'

import {AnnouncementTool as AnnouncementToolComponent} from '#/plugin/announcement//tools/announcement/components/tool'
import {actions, reducer, selectors} from '#/plugin/announcement/tools/announcement/store'

const AnnouncementTool = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    state => ({
      posts: selectors.posts(state)
    }),
    dispatch => ({
      openDetail(id) {
        dispatch(actions.openDetail(id))
        dispatch(actions.updateView(id))
      },
      resetDetail() {
        dispatch(actions.resetDetail())
      },
      resetForm(data, isNew) {
        dispatch(formActions.resetForm(selectors.STORE_NAME+'.announcementForm', data, isNew))
      }
    })
  )(AnnouncementToolComponent)
)

export {
  AnnouncementTool
}
