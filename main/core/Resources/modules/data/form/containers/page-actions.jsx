import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {url} from '#/main/app/api'
import {FormPageActions} from '#/main/core/layout/form/components/page-actions.jsx'

import {actions} from '#/main/core/data/form/actions'
import {select} from '#/main/core/data/form/selectors'

const FormPageActionsContainer = connect(
  (state, ownProps) => ({
    new: select.isNew(select.form(state, ownProps.formName)),
    data: select.data(select.form(state, ownProps.formName)),
    saveEnabled: select.saveEnabled(select.form(state, ownProps.formName)),
    pendingChanges: select.pendingChanges(select.form(state, ownProps.formName))
  }),
  (dispatch, ownProps) => ({
    save(targetUrl) {
      dispatch(actions.saveForm(ownProps.formName, targetUrl))
    },
    cancel() {
      dispatch(actions.cancelChanges(ownProps.formName))
    }
  }),
  (stateProps, dispatchProps, ownProps) => Object.assign({}, ownProps, {
    save: ownProps.save ? Object.assign({}, ownProps.save, {
      disabled: !stateProps.saveEnabled || ownProps.save.disabled,
      onClick: () => {
        if (ownProps.target) {
          dispatchProps.save(url(
            typeof ownProps.target === 'function' ? ownProps.target(stateProps.data, stateProps.new) : ownProps.target
          ))
        }
      }
    }) : {
      type: 'callback',
      disabled: !stateProps.saveEnabled,
      callback: () => {
        if (ownProps.target) {
          dispatchProps.save(url(
            typeof ownProps.target === 'function' ? ownProps.target(stateProps.data, stateProps.new) : ownProps.target
          ))
        }
      }
    },
    cancel: ownProps.cancel ? Object.assign({}, ownProps.cancel, {
      // if there is a custom action, we don't check for pending changes
      // because this is mostly used to add a way to leave the form.
      disabled: ownProps.cancel.disabled,
      // append the reset form callback to the defined action
      onClick: () => dispatchProps.cancel()
    }) : {
      type: 'callback',
      disabled: !stateProps.pendingChanges,
      callback: () => dispatchProps.cancel()
    }
  })
)(FormPageActions)

FormPageActionsContainer.propTypes = {
  formName: T.string.isRequired,
  target: T.oneOfType([T.string, T.array, T.func])
}

export {
  FormPageActionsContainer
}
