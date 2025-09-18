import {connect} from 'react-redux'

import {hasPermission} from '#/main/app/security'
import {selectors as toolSelectors} from '#/main/core/tool'

import {AnnouncementPost as AnnouncementPostComponent} from '#/plugin/announcement/tools/announcement/components/post'
import {actions, selectors} from '#/plugin/announcement/tools/announcement/store'

const AnnouncementPost = connect(
  state => ({
    path: toolSelectors.path(state),
    announcement: selectors.detail(state),
    editable: hasPermission('edit', toolSelectors.tool(state))
  }),
  dispatch => ({
    remove(announcePost) {
      dispatch(actions.removeAnnounce(announcePost))
    },
    exportPDF(announcePost) {
      dispatch(actions.exportPDF(announcePost))
    }
  })
)(AnnouncementPostComponent)

export {
  AnnouncementPost
}
