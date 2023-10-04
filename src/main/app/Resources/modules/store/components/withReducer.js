import React from 'react'

import {useReducer} from '#/main/app/store/hooks/useReducer'

/**
 * HOC permitting to dynamically append the reducer needed by a container.
 *
 * @param {string} key
 * @param {object} reducer
 *
 * @return {func}
 */
function withReducer(key, reducer) {
  return function appendReducers(WrappedComponent) {
    const WithReducer = (props) => {
      // this will mount the requested reducer into the current redux store
      useReducer(key, reducer)

      return (
        <WrappedComponent {...props} />
      )
    }

    WithReducer.displayName = `WithReducer(${WrappedComponent.displayName || WrappedComponent.name || 'Component'})`

    return WithReducer
  }
}

export {
  withReducer
}
