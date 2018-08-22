import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {AnnouncementResource as AnnouncementResourceComponent} from '#/plugin/announcement/resources/announcement/components/resource'
import {actions, reducer, selectors} from '#/plugin/announcement/resources/announcement/store'

const AnnouncementResource = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    state => ({
      aggregateId: selectors.aggregateId(state),
      posts: selectors.posts(state),
      roles: selectors.workspaceRoles(state)
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
      },
      initFormDefaultRoles(roleIds) {
        dispatch(formActions.updateProp(selectors.STORE_NAME+'.announcementForm', 'roles', roleIds))
      }
    })
  )(AnnouncementResourceComponent)
)

export {
  AnnouncementResource
}
