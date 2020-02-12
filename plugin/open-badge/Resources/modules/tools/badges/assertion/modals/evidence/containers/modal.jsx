import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as listActions} from '#/main/app/content/list/store'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'

import {EvidenceModal as EvidenceModalComponent} from '#/plugin/open-badge/tools/badges/assertion/modals/evidence/components/modal'
import {reducer, selectors} from '#/plugin/open-badge/tools/badges/assertion/modals/evidence/store'

const EvidenceModal = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      isNew: formSelectors.isNew(formSelectors.form(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      initForm(evidence = null) {
        if (!evidence) {
          dispatch(formActions.resetForm(selectors.STORE_NAME, {}, true))
        } else {
          dispatch(formActions.resetForm(selectors.STORE_NAME, evidence, false))
        }
      },

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
