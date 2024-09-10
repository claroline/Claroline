import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as resourceSelectors} from '#/main/core/resource'


import {AnnouncementPost as AnnouncementPostComponent} from '#/plugin/announcement/resources/announcement/components/post'
import {actions, selectors} from '#/plugin/announcement/resources/announcement/store'

const AnnouncementPost = connect(
  state => ({
    path: resourceSelectors.path(state),
    aggregateId: selectors.aggregateId(state),
    announcement: selectors.detail(state),
    workspaceRoles: selectors.workspaceRoles(state),
    editable: hasPermission('edit', resourceSelectors.resourceNode(state))
  }),
  dispatch => ({
    remove(aggregateId, announcePost) {
      dispatch(actions.removeAnnounce(aggregateId, announcePost))
    },
    exportPDF(aggregateId, announcePost) {
      dispatch(actions.exportPDF(aggregateId, announcePost))
    }
  })
)(AnnouncementPostComponent)

export {
  AnnouncementPost
}
