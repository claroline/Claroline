import React, {Fragment, Component, createElement} from 'react'
import {PropTypes as T} from 'prop-types'

import {theme} from '#/main/app/config'
import {withReducer} from '#/main/app/store/components/withReducer'
import {makeCancelable} from '#/main/app/api'
import {getResource} from '#/main/core/resources'

import {ContentLoader} from '#/main/app/content/components/loader'

const Resource = props => {
  if (props.loaded) {
    return (
      <Fragment>
        {props.children}

        {props.styles && props.styles.map(style =>
          <link key={style} rel="stylesheet" type="text/css" href={theme(style)} />
        )}
      </Fragment>
    )
  }

  return (
    <ContentLoader
      size="lg"
      description="Nous chargeons votre ressource"
    />
  )
}

Resource.propTypes = {
  loaded: T.bool.isRequired,
  styles: T.arrayOf(T.string),
  children: T.node
}

class ResourceMain extends Component {
  constructor(props) {
    super(props)

    this.state = {
      appLoaded: false,
      app: null,
      component: null,
      styles: []
    }
  }

  componentDidMount() {
    this.loadApp()
    if (!this.props.loaded) {
      this.props.open(this.props.resourceSlug)
    }
  }

  componentDidUpdate(prevProps) {
    if (this.props.resourceType !== prevProps.resourceType) {
      if (this.pending) {
        this.pending.cancel()
        this.pending = null
      }

      this.loadApp()
    }

    if (this.props.resourceSlug !== prevProps.resourceSlug) {
      this.props.close(prevProps.resourceSlug)
    }

    if (!this.props.loaded && this.props.loaded !== prevProps.loaded) {
      this.props.open(this.props.resourceSlug)
    }
  }

  componentWillUnmount() {
    if (this.pending) {
      this.pending.cancel()
      this.pending = null
    }
    this.props.close(this.props.resourceSlug)
  }

  loadApp() {
    if (this.props.resourceType && !this.pending) {
      this.setState({appLoaded: false})
      this.pending = makeCancelable(getResource(this.props.resourceType))

      this.pending.promise
        .then(
          (resolved) => {
            if (resolved.default) {
              this.setState({
                appLoaded: true,
                // I build the store here because if I do it in the render()
                // it will be called many times and will cause multiple mount/unmount of the app
                app: withReducer(this.props.resourceType, resolved.default.store)(Resource),
                component: resolved.default.component,
                styles: resolved.default.styles
              })
            }
          },
          (errors) => {
            // TODO : find better.
            /* eslint-disable no-console */
            console.error(errors)
            /* eslint-enable no-console */
          }
        )
        .then(
          () => this.pending = null,
          () => this.pending = null
        )
    }
  }

  render() {
    if (!this.props.loaded || !this.state.appLoaded) {
      return (
        <ContentLoader
          size="lg"
          description="Nous chargeons votre ressource"
        />
      )
    }

    if (this.state.app) {
      return createElement(this.state.app, {
        loaded: this.props.loaded,
        styles: this.state.styles,
        children: this.state.component && createElement(this.state.component, {
          path: this.props.path
        })
      })
    }
  }
}

ResourceMain.propTypes = {
  path: T.string.isRequired,
  resourceSlug: T.string.isRequired,
  resourceType: T.string,

  loaded: T.bool.isRequired,
  open: T.func.isRequired,
  close: T.func.isRequired
}

export {
  ResourceMain
}
