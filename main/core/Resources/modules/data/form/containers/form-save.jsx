import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import invariant from 'invariant'

import {t} from '#/main/core/translation'
import {generateUrl} from '#/main/core/api/router'

import {actions} from '#/main/core/data/form/actions'
import {select} from '#/main/core/data/form/selectors'

// todo remove me. use page-actions.jsx instead (check registration form which don't use page actions)

// this is a HOC to allow any button component with the correct interface to become a form submit
// it will set `title`, `disabled`, `action` props of the passed btn component
function makeSaveAction(formName, actionsMap) {
  return (ButtonComponent) => {
    const SaveWrapper = props => {
      // calculate actions
      const formActions = actionsMap(props.formData)
      const currentAction = props.new ? formActions.create : formActions.update

      invariant(currentAction, `Cannot get the correct action for form "${formName}".`)

      return (
        <ButtonComponent
          id={`save-${formName}-btn`}
          icon="fa fa-floppy-o"
          title={t('save')}
          primary={true}
          disabled={!props.saveEnabled}
          action={() => {
            props.saveForm(
              currentAction instanceof Array ? generateUrl(...currentAction) : currentAction
            )
          }}

          {...props} // we put passed props at the end to be able to override it
        />
      )
    }

    SaveWrapper.propTypes = {
      new: T.bool.isRequired,
      formData: T.object.isRequired,
      saveEnabled: T.bool.isRequired,
      saveForm: T.func.isRequired
    }

    SaveWrapper.displayName = `SaveWrapper(${formName})`

    // connect it to the store
    return connect(
      state => ({
        new: select.isNew(select.form(state, formName)),
        formData: select.data(select.form(state, formName)),
        saveEnabled: select.saveEnabled(select.form(state, formName))
      }),
      dispatch => ({
        saveForm: (targetUrl) => dispatch(actions.saveForm(formName, targetUrl))
      })
    )(SaveWrapper)
  }
}

export {
  makeSaveAction
}
