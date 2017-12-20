import {connect} from 'react-redux'

import {actions} from '#/main/core/user/profile/actions'

function mapStateToProps(state) {
  return {
    facets: state.facets
  }
}

function mapDispatchToProps(dispatch) {
  return {
    openFacet(id) {
      dispatch(actions.openFacet(id))
    }
  }
}

/**
 * Connects a profile component to the store.
 *
 * @todo find a way to implement without the double `connect`.
 *
 * @param {function} customMapStateToProps
 * @param {function} customMapDispatchToProps
 *
 * @returns {function}
 */
function connectProfile(customMapStateToProps = () => ({}), customMapDispatchToProps = () => ({})) {
  return (ProfileComponent) => connect(
    customMapStateToProps,
    customMapDispatchToProps
  )(connect(mapStateToProps, mapDispatchToProps)(ProfileComponent))
}

export {
  connectProfile
}
