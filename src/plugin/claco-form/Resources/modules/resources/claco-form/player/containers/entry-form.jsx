import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {actions as formActions} from '#/main/app/content/form/store/actions'
import {notEmpty} from '#/main/app/data/types/validators'
import {isFieldDisplayed} from '#/main/app/content/form/parameters/utils'
import {selectors as contextSelectors} from '#/main/app/context'
import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {selectors} from '#/plugin/claco-form/resources/claco-form/store'
import {actions} from '#/plugin/claco-form/resources/claco-form/player/store'
import {EntryForm as EntryFormComponent} from '#/plugin/claco-form/resources/claco-form/player/components/entry-form'

const EntryForm = withRouter(connect(
  state => ({
    currentUser: securitySelectors.currentUser(state),
    path: resourceSelectors.path(state),
    resourceName: resourceSelectors.name(state),
    contactEmail: contextSelectors.contactEmail(state),

    canAdministrate: selectors.canManageCurrentEntry(state),
    canAddEntry: selectors.canAddEntry(state),
    clacoFormId: selectors.clacoForm(state).id,
    fields: selectors.visibleFields(state),
    useTemplate: selectors.useTemplate(state),
    template: selectors.template(state),
    showConfirm: selectors.showConfirm(state),
    confirmMessage: selectors.confirmMessage(state),
    titleLabel: selectors.params(state).title_field_label,
    displayMetadata: selectors.params(state).display_metadata,
    isNew: formSelect.isNew(formSelect.form(state, selectors.STORE_NAME+'.entries.current')),
    errors: formSelect.errors(formSelect.form(state, selectors.STORE_NAME+'.entries.current')),
    entry: formSelect.data(formSelect.form(state, selectors.STORE_NAME+'.entries.current')),
    pendingChanges: formSelect.pendingChanges(formSelect.form(state, selectors.STORE_NAME+'.entries.current')),
    categories: selectors.categories(state)
  }),
  (dispatch) => ({
    saveForm(entry, fields, isNew, navigate, path) {
      // validate required fields
      const errors = {
        title: notEmpty(entry.title),
        values: {}
      }
      const requiredFields = fields.filter(field => field.required && isFieldDisplayed(field, fields, entry.values))
      errors.values = requiredFields.reduce((fieldErrors, field) => Object.assign(fieldErrors, {
        [field.id]: notEmpty(entry.values[field.id])
      }), {})

      dispatch(formActions.setErrors(selectors.STORE_NAME+'.entries.current', errors))

      if (isNew) {
        dispatch(formActions.saveForm(selectors.STORE_NAME+'.entries.current', ['apiv2_clacoformentry_create'])).then((data) => {
          dispatch(actions.addCreatedEntry(data))
          navigate(`${path}/entries/${data.id}`)
        }, () => true)
      } else {
        dispatch(formActions.saveForm(selectors.STORE_NAME+'.entries.current', ['apiv2_clacoformentry_update', {id: entry.id}]))
      }
    },
    updateFormProp(propName, propValue) {
      dispatch(formActions.updateProp(selectors.STORE_NAME+'.entries.current', propName, propValue))
    },
    setErrors(errors) {
      dispatch(formActions.setErrors(selectors.STORE_NAME+'.entries.current', errors))
    },
    addCategory(category) {
      dispatch(actions.addCategory(category))
    },
    removeCategory(categoryId) {
      dispatch(actions.removeCategory(categoryId))
    }
  })
)(EntryFormComponent))

export {
  EntryForm
}
