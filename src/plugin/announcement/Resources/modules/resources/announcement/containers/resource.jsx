import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {AnnouncementResource as AnnouncementResourceComponent} from '#/plugin/announcement/resources/announcement/components/resource'
import {actions, reducer, selectors} from '#/plugin/announcement/resources/announcement/store'

const AnnouncementResource = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    state => ({
      posts: selectors.posts(state),
      announcement: selectors.announcement(state)
    }),
    dispatch => ({
      openDetail(id) {
        dispatch(actions.openDetail(id))
      },
      resetDetail() {
        dispatch(actions.resetDetail())
      },
      resetForm(data, isNew) {
        dispatch(formActions.resetForm(selectors.STORE_NAME+'.announcementForm', data, isNew))
      }
    })
  )(AnnouncementResourceComponent)
)

export {
  AnnouncementResource
}
