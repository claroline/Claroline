import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as listActions} from '#/main/app/content/list/store'

import {
  actions as formActions
} from '#/main/app/content/form/store'

import {EvidenceModal as EvidenceModalComponent} from '#/plugin/open-badge/tools/badges/modals/evidence/components/modal'
import {reducer, selectors} from '#/plugin/open-badge/tools/badges/modals/evidence/store'

const EvidenceModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    null,
    (dispatch) => ({
      saveEvidence(assertion) {
        dispatch(formActions.saveForm(selectors.STORE_NAME, ['apiv2_evidence_create_at', {assertion: assertion.id}]))
        dispatch(listActions.invalidateData('badges.assertion.evidences'))
      }
    })
  )(EvidenceModalComponent)
)

export {
  EvidenceModal
}
