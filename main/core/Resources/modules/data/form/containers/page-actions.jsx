import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {getUrl} from '#/main/core/api/router'
import {FormPageActions as FormPageActionsComponent} from '#/main/core/layout/form/components/page-actions.jsx'

import {actions} from '#/main/core/data/form/actions'
import {select} from '#/main/core/data/form/selectors'

const FormPageActions = props =>
  <FormPageActionsComponent
    {...props}
  />

FormPageActions.propTypes = {
  formName: T.string.isRequired,
  target: T.oneOfType([T.string, T.array, T.func]),

  open: T.shape({
    label: T.string,
    icon: T.string,
    action: T.oneOfType([T.string, T.func]),
    disabled: T.bool
  }),

  save: T.shape({
    label: T.string,
    icon: T.string,
    action: T.func,
    disabled: T.bool
  }),

  cancel: T.shape({
    label: T.string,
    icon: T.string,
    action: T.func,
    disabled: T.bool
  })
}

const FormPageActionsContainer = connect(
  (state, ownProps) => ({
    new: select.isNew(select.form(state, ownProps.formName)),
    data: select.data(select.form(state, ownProps.formName)),
    saveEnabled: select.saveEnabled(select.form(state, ownProps.formName)),
    pendingChanges: select.saveEnabled(select.form(state, ownProps.formName))
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
    save: Object.assign({}, ownProps.save || {}, {
      disabled: !stateProps.saveEnabled || (ownProps.save && ownProps.save.disabled),
      action: () => {
        if (ownProps.save && ownProps.save.action) {
          ownProps.save.action(stateProps.data, stateProps.new)
        }

        if (ownProps.target) {
          const targetUrl = getUrl(
            typeof ownProps.target === 'function' ? ownProps.target(stateProps.data, stateProps.new) : ownProps.target
          )

          dispatchProps.save(targetUrl)
        }
      }
    }),
    cancel: Object.assign({}, ownProps.cancel || {}, {
      disabled: (!stateProps.pendingChanges && (!ownProps.cancel || !ownProps.cancel.action)) || (ownProps.cancel && ownProps.cancel.disabled),
      action: () => {
        if (ownProps.cancel && ownProps.cancel.action) {
          ownProps.cancel.action()
        }

        dispatchProps.cancel()
      }
    })
  })
)(FormPageActions)

export {
  FormPageActionsContainer
}
