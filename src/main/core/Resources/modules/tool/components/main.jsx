import React, {createElement, Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {Helmet} from 'react-helmet'

import {theme} from '#/main/theme/config'
import {trans} from '#/main/app/intl/translation'
import {withReducer} from '#/main/app/store/reducer'
import {makeCancelable} from '#/main/app/api'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ContentForbidden} from '#/main/app/content/components/forbidden'
import {ContentNotFound} from '#/main/app/content/components/not-found'

import {getTool} from '#/main/core/tool/utils'

const Tool = props => {
  if (props.loaded) {
    return (
      <>
        {props.children}

        {0 !== props.styles.length &&
          <Helmet>
            {props.styles.map(style =>
              <link key={style} rel="stylesheet" type="text/css" href={theme(style)} />
            )}
          </Helmet>
        }
      </>
    )
  }

  return (
    <ContentLoader
      size="lg"
      description={trans('loading', {}, 'tools')}
    />
  )
}

Tool.propTypes = {
  loaded: T.bool.isRequired,
  styles: T.arrayOf(T.string),
  children: T.node
}

Tool.defaultProps = {
  styles: []
}

class ToolMain extends Component {
  constructor(props) {
    super(props)

    this.state = {
      appLoaded: false,
      app: null,
      component: null,
      styles: []
    }

    this.open = this.open.bind(this)
  }

  componentDidMount() {
    if (this.props.name) {
      this.loadApp().then(() => {
        if (!this.props.loaded) {
          // open current tool
          this.open()
        }
      })
    }
  }

  componentDidUpdate(prevProps) {
    let appPromise
    if (this.props.name && this.props.name !== prevProps.name) {
      if (this.pendingApp) {
        this.pendingApp.cancel()
        this.pendingApp = null
      }

      if (this.pending) {
        this.pending.cancel()
        this.pending = null
      }

      appPromise = this.loadApp()
    } else {
      appPromise = Promise.resolve(true)
    }

    appPromise.then(() => {
      if ((this.props.name && this.props.name !== prevProps.name) || (!this.props.loaded && this.props.loaded !== prevProps.loaded)) {
        if (!this.pending) {
          // open current tool
          this.open()
        }
      }
    })
  }

  open() {
    this.pending = makeCancelable(
      this.props.open(this.props.name, this.props.contextType, this.props.contextId)
    )

    this.pending.promise
      .then(
        () => this.pending = null,
        () => this.pending = null
      )
  }

  loadApp() {
    if (!this.pendingApp) {
      this.setState({appLoaded: false})

      this.pendingApp = makeCancelable(
        getTool(this.props.name, this.props.contextType)
      )

      this.pendingApp.promise
        .then(
          (resolved) => {
            if (resolved.default) {
              this.setState({
                appLoaded: true,
                // I build the store here because if I do it in the render()
                // it will be called many times and will cause multiple mount/unmount of the app
                app: resolved.default.store ? withReducer(this.props.name, resolved.default.store)(Tool) : Tool,
                component: resolved.default.component,
                styles: resolved.default.styles
              })
            }
          },
          // TODO : properly handle error
          (error) => console.error(error) /* eslint-disable-line no-console */
        )
        .then(
          () => this.pendingApp = null,
          () => this.pendingApp = null
        )
    }

    return this.pendingApp.promise
  }

  componentWillUnmount() {
    if (this.pendingApp) {
      this.pendingApp.cancel()
      this.pendingApp = null
    }

    if (this.pending) {
      this.pending.cancel()
      this.pending = null
    }
  }

  render() {
    if (!this.state.appLoaded) {
      return (
        <ContentLoader
          size="lg"
          description={trans('loading', {}, 'tools')}
        />
      )
    }

    if (this.props.notFound) {
      return (
        <ContentNotFound
          size="lg"
          title={trans('not_found', {}, 'tools')}
          description={trans('not_found_desc', {}, 'tools')}
        />
      )
    }

    if (this.props.accessDenied) {
      return (
        <ContentForbidden
          size="lg"
          title={trans('forbidden', {}, 'tools')}
          description={trans('forbidden_desc', {}, 'tools')}
        />
      )
    }

    if (this.state.app) {
      return createElement(this.state.app, {
        path: this.props.path,
        loaded: this.props.loaded,
        styles: this.state.styles,
        children: this.state.component && createElement(this.state.component, {
          path: this.props.path
        })
      })
    }

    return null
  }
}

ToolMain.propTypes = {
  path: T.string,
  name: T.string.isRequired,
  contextType: T.string.isRequired,
  contextId: T.string,
  loaded: T.bool.isRequired,
  notFound: T.bool.isRequired,
  accessDenied: T.bool.isRequired,
  open: T.func.isRequired
}

ToolMain.defaultProps = {
  path: ''
}

export {
  ToolMain
}
