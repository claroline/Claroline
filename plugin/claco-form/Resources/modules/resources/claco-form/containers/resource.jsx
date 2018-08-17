import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {currentUser} from '#/main/core/user/current'
import {makeId} from '#/main/core/scaffolding/id'
import {now} from '#/main/core/scaffolding/date'

import {reducer, selectors} from '#/plugin/claco-form/resources/claco-form/store'
import {actions as entryActions} from '#/plugin/claco-form/resources/claco-form/player/entry/store'
import {ClacoFormResource as ClacoFormResourceComponent} from '#/plugin/claco-form/resources/claco-form/components/resource'

const authenticatedUser = currentUser()

const ClacoFormResource = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
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
      openEntryForm(id, clacoFormId, fields = []) {
        const defaultValue = {
          id: makeId(),
          values: {},
          clacoForm: {
            id: clacoFormId
          },
          user: authenticatedUser,
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
      loadEntryUser(entryId) {
        dispatch(entryActions.loadEntryUser(entryId))
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

export {
  ClacoFormResource
}
