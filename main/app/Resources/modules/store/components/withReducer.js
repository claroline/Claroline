import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {ReactReduxContext} from 'react-redux'

class ReducerLoader extends Component {
  constructor(props) {
    super(props)

    props.store.injectReducer(props.keyName, props.reducer)
  }

  render() {
    if (!this.props.storeState[this.props.keyName]) {
      return null
    }

    return this.props.children
  }
}

ReducerLoader.propTypes = {
  store: T.object.isRequired,
  storeState: T.object,
  keyName: T.string.isRequired,
  reducer: T.func.isRequired,
  children: T.any
}

function getDisplayName(WrappedComponent) {
  return WrappedComponent.displayName || WrappedComponent.name || 'Component'
}

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
    const WithReducer = (props) => (
      <ReactReduxContext.Consumer>
        {({ store, storeState }) => (
          <ReducerLoader
            store={store}
            storeState={storeState}
            keyName={key}
            reducer={reducer}
          >
            <WrappedComponent {...props} />
          </ReducerLoader>
        )}
      </ReactReduxContext.Consumer>
    )

    WithReducer.displayName = getDisplayName(WrappedComponent)

    return WithReducer
  }
}

export {
  withReducer
}
