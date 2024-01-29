import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import invariant from 'invariant'
import get from 'lodash/get'

import {FormData as FormDataComponent} from '#/main/app/content/form/components/data'
import {actions, selectors} from '#/main/app/content/form/store'

const FormData = connect(
  (state, ownProps) => {
    // get the root of the form in the store
    const formState = selectors.form(state, ownProps.name)
    invariant(undefined !== formState, `Try to connect form on undefined store '${ownProps.name}'.`)

    let data = selectors.data(formState)
    if (ownProps.dataPart) {
      // just select what is related to the managed data part
      data = get(data, ownProps.dataPart)
    }

    return {
      mode: selectors.mode(formState),
      data: data
    }
  },
  (dispatch, ownProps) => ({
    setMode(mode) {
      dispatch(actions.setMode(ownProps.name, mode))
    }
  })
)(FormDataComponent)

FormData.propTypes = {
  /**
   * The name of the data in the form.
   *
   * It should be the key in the store where the list has been mounted
   * (aka where `makeFormReducer()` has been called).
   */
  name: T.string.isRequired,

  /**
   * Permits to connect the form on a sub-part of the data.
   * This is useful when the form is broken in multiple steps/pages
   *
   * It MUST be a valid lodash/get selector.
   */
  dataPart: T.string,

  /**
   * Do we need to show the form buttons ?
   */
  buttons: T.bool,

  /**
   * The API target of the Form (only used if props.buttons === true).
   *
   * NB. It can be a route definition or a function to calculate the final route.
   * If a function is provided it's called with the current data & new flag as param.
   */
  target: T.oneOfType([T.string, T.array, T.func]),

  /**
   * A custom save action for the form (only used if props.buttons === true).
   *
   * NB. If a target is provided, the api call will be made before executing the custom action.
   */
  save: T.shape({
    type: T.string.isRequired,
    disabled: T.bool
  }),

  /**
   * A custom cancel action for the form (only used if props.buttons === true).
   */
  cancel: T.shape({
    type: T.string.isRequired,
    disabled: T.bool
  })
}

FormData.defaultProps = {
  buttons: false
}

export {
  FormData
}
