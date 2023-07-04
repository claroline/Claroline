import {connect} from 'react-redux'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {AnnouncesEditor as AnnouncesEditorComponent} from '#/plugin/announcement/resources/announcement/editor/components/editor'
import {actions as formActions} from '#/main/app/content/form/store'
import {selectors} from '#/plugin/announcement/resources/announcement/store'

const AnnouncesEditor = connect(
  state => ({
    path: resourceSelectors.path(state),
    announcement: selectors.announcement(state)
  }),
  dispatch => ({
    saveForm(aggregateId) {
      dispatch(formActions.saveForm(
        selectors.STORE_NAME+'.announcementForm',
        ['claro_announcement_aggregate_update', {aggregateId: aggregateId}]
      ))
    }
  })
)(AnnouncesEditorComponent)

export {
  AnnouncesEditor
}
