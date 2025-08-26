import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {selectors} from '#/plugin/claco-form/resources/claco-form/store'
import {actions} from '#/plugin/claco-form/resources/claco-form/player/store'
import {Entry as EntryComponent} from '#/plugin/claco-form/resources/claco-form/player/components/entry'

const Entry = withRouter(connect(
  (state) => ({
    path: resourceSelectors.path(state),
    entry: formSelect.data(formSelect.form(state, selectors.STORE_NAME+'.entries.current')),

    canEdit: selectors.canEditCurrentEntry(state),
    canViewEntry: selectors.canOpenCurrentEntry(state),
    canAdministrate: selectors.canManageCurrentEntry(state),
    canDownload: selectors.canDownload(state),

    fields: selectors.visibleFields(state),
    helpMessage: selectors.params(state).helpMessage,
    displayMetadata: selectors.params(state).display_metadata,
    displayCategories: selectors.params(state).display_categories,
    isOwner: selectors.isCurrentEntryOwner(state),
    useTemplate: selectors.useTemplate(state),
    template: selectors.template(state),
    titleLabel: selectors.params(state).title_field_label
  }),
  (dispatch) => ({
    deleteEntry(entry) {
      return dispatch(actions.deleteEntry(entry))
    },
    switchEntryStatus(entryId) {
      dispatch(actions.switchEntryStatus(entryId))
    },
    switchEntryLock(entryId) {
      dispatch(actions.switchEntryLock(entryId))
    },
    downloadEntryPdf(entryId) {
      return dispatch(actions.downloadEntryPdf(entryId))
    },
    changeEntryOwner(entryId, userId) {
      dispatch(actions.changeEntryOwner(entryId, userId))
    }
  })
)(EntryComponent))

export {
  Entry
}
