import {connect} from 'react-redux'

import {
  actions as formActions,
  selectors as formSelectors
} from '#/main/app/content/form/store'

import {EventForm as EventFormComponent} from '#/plugin/agenda/event/components/form'

const EventForm = connect(
  (state, ownProps) => ({
    data: formSelectors.data(formSelectors.form(state, ownProps.name)),
    isNew: formSelectors.isNew(formSelectors.form(state, ownProps.name)),
    saveEnabled: formSelectors.saveEnabled(formSelectors.form(state, ownProps.name))
  }),
  (dispatch) => ({
    save(name, target, onSave) {
      dispatch(formActions.save(name, target))
        .then((response) => {
          if (onSave) {
            onSave(response)
          }
        })
    }
  })
)(EventFormComponent)

export {
  EventForm
}
