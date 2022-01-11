import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'

import {actions as formActions} from '#/main/app/content/form/store'
import {actions as historyActions} from '#/plugin/wiki/resources/wiki/history/store'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {hasPermission} from '#/main/app/security'

import {WikiResource as WikiResourceComponent} from '#/plugin/wiki/resources/wiki/components/resource'
import {selectors} from '#/plugin/wiki/resources/wiki/store/selectors'
import {reducer} from '#/plugin/wiki/resources/wiki/store/reducer'

const WikiResource = withRouter(
  withReducer(selectors.STORE_NAME, reducer)(
    connect(
      (state) => ({
        wiki: selectors.wiki(state),
        canEdit: hasPermission('edit', resourceSelect.resourceNode(state)),
        canExport: hasPermission('export', resourceSelect.resourceNode(state))
      }),
      (dispatch) => ({
        resetForm: (formData) => dispatch(formActions.resetForm(selectors.STORE_NAME + '.wikiForm', formData)),
        setCurrentHistorySection: (sectionId = null) => dispatch(historyActions.setCurrentHistorySection(sectionId)),
        setCurrentHistoryVersion: (sectionId = null, contributionId = null) => dispatch(historyActions.setCurrentHistoryVersion(sectionId, contributionId)),
        setCurrentHistoryCompareSet: (sectionId = null, id1 = null, id2 = null) => dispatch(historyActions.setCurrentHistoryCompareSet(sectionId, id1, id2))
      })
    )(WikiResourceComponent)
  )
)

export {
  WikiResource
}
