import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {withReducer} from '#/main/app/store/components/withReducer'
import {now} from '#/main/app/intl/date'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {makeId} from '#/main/core/scaffolding/id'

import {reducer, selectors} from '#/plugin/claco-form/resources/claco-form/store'
import {actions as entryActions} from '#/plugin/claco-form/resources/claco-form/player/store'
import {ClacoFormResource as ClacoFormResourceComponent} from '#/plugin/claco-form/resources/claco-form/components/resource'

const ClacoFormResource = withRouter(
  withReducer(selectors.STORE_NAME, reducer)(
    connect(
      (state) => ({
        currentUser: securitySelectors.currentUser(state),
        clacoForm: selectors.clacoForm(state),
        canEdit: selectors.canAdministrate(state),
        canAddEntry: selectors.canAddEntry(state),
        canSearchEntry: selectors.canSearchEntry(state),
        defaultHome: selectors.params(state) ? selectors.params(state).default_home : null
      }),
      (dispatch) => ({
        resetForm(formData) {
          dispatch(formActions.resetForm(selectors.STORE_NAME+'.clacoFormForm', formData))
        },
        openEntryForm(id, clacoFormId, fields = [], currentUser) {
          const defaultValue = {
            id: makeId(),
            values: {},
            clacoForm: {
              id: clacoFormId
            },
            user: currentUser,
            categories: [],
            keywords: []
          }
          fields.forEach(f => {
            if (f.type === 'date') {
              defaultValue.values[f.id] = now()
            }
          })

          dispatch(entryActions.openForm(selectors.STORE_NAME+'.entries.current', id, defaultValue))
        },
        resetEntryForm() {
          dispatch(formActions.resetForm(selectors.STORE_NAME+'.entries.current', {}, true))
        },
        loadEntryUser(entryId, currentUser) {
          if (currentUser) {
            dispatch(entryActions.loadEntryUser(entryId))
          }
        },
        resetEntryUser() {
          dispatch(entryActions.resetEntryUser())
        },
        loadAllUsedCountries(clacoFormId) {
          dispatch(entryActions.loadAllUsedCountries(clacoFormId))
        }
      })
    )(ClacoFormResourceComponent)
  )
)

export {
  ClacoFormResource
}
