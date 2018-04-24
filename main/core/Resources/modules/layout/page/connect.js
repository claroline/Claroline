import merge from 'lodash/merge'
import {connect} from 'react-redux'

import {actions as alertActions} from '#/main/core/layout/alert/actions'
import {select as alertSelect} from '#/main/core/layout/alert/selectors'

import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {select as modalSelect} from '#/main/core/layout/modal/selectors'

import {select as pageSelect} from '#/main/core/layout/page/selectors'

/**
 * Gets page data and config from redux store.
 *
 * NB. we will enable page features based on what we find in the store.
 *
 * @param {object} state
 *
 * @returns {object}
 */
function mapStateToProps(state) {
  const newProps = {}

  newProps.embedded = pageSelect.embedded(state)

  // grab data for optional features
  newProps.hasAlerts = pageSelect.hasAlerts(state)
  if (newProps.hasAlerts) {
    newProps.alerts = alertSelect.displayedAlerts(state)
  }

  newProps.hasModals = pageSelect.hasModals(state)
  if (newProps.hasModals) {
    newProps.modal = modalSelect.modal(state)
  }

  return newProps
}

/**
 * Injects page features actions.
 * NB. we inject actions for all features, `mergeProps` will only pick the one for enabled features.
 *
 * @param {function} dispatch
 *
 * @return {object}
 */
function mapDispatchToProps(dispatch) {
  return {
    // alerts
    removeAlert(type, message) {
      dispatch(alertActions.removeAlert(type, message))
    },

    // modal
    showModal(modalType, modalProps) {
      dispatch(modalActions.showModal(modalType, modalProps))
    },
    fadeModal() {
      dispatch(modalActions.fadeModal())
    },
    hideModal() {
      dispatch(modalActions.hideModal())
    }
  }
}

/**
 * Generates the final container props based on store available data.
 *
 * @param {object} stateProps    - the injected store data
 * @param {object} dispatchProps - the injected store actions
 * @param {object} ownProps      - the props passed to the react components
 *
 * @returns {object} - the final props object that will be passed to Page container
 */
function mergeProps(stateProps, dispatchProps, ownProps) {
  const props = {}

  props.embedded = stateProps.embedded

  if (stateProps.hasAlerts) {
    props.alerts = stateProps.alerts
    props.removeAlert = dispatchProps.removeAlert
  }

  if (stateProps.hasModals) {
    props.modal = stateProps.modal
    props.showModal = dispatchProps.showModal
    props.fadeModal = dispatchProps.fadeModal
    props.hideModal = dispatchProps.hideModal
  }

  return merge({}, props, ownProps)
}

/**
 * Connects a page component to the store.
 *
 * @todo find a way to implement without the double `connect`.
 *
 * @param {function} customMapStateToProps
 * @param {function} customMapDispatchToProps
 *
 * @returns {function}
 */
function connectPage(customMapStateToProps = () => ({}), customMapDispatchToProps = () => ({})) {
  return (PageComponent) => connect(
    customMapStateToProps,
    customMapDispatchToProps
  )(connect(mapStateToProps, mapDispatchToProps, mergeProps)(PageComponent))
}

export {
  connectPage
}
