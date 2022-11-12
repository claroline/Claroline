import React, {createElement, Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import {Helmet} from 'react-helmet'

import {theme} from '#/main/theme/config'
import {trans} from '#/main/app/intl/translation'
import {withReducer} from '#/main/app/store/components/withReducer'
import {makeCancelable} from '#/main/app/api'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ContentForbidden} from '#/main/app/content/components/forbidden'
import {ContentNotFound} from '#/main/app/content/components/not-found'

import {constants} from '#/main/core/tool/constants'
import {getTool} from '#/main/core/tools'
import {getTool as getAdminTool} from '#/main/core/administration'

const Tool = props => {
  if (props.loaded) {
    return (
      <Fragment>
        {props.children}

        {0 !== props.styles.length &&
          <Helmet>
            {props.styles.map(style =>
              <link key={style} rel="stylesheet" type="text/css" href={theme(style)} />
            )}
          </Helmet>
        }
      </Fragment>
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
    this.loadApp().then(() => {
      if (!this.props.loaded) {
        // open current tool
        this.open()
      }
    })
  }

  componentDidUpdate(prevProps) {
    let appPromise
    if (this.props.toolName && this.props.toolName !== prevProps.toolName) {
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
      if (!this.props.loaded && this.props.loaded !== prevProps.loaded) {
        if (!this.pending) {
          // close previous tool
          if (this.props.toolName && prevProps.toolName && this.props.toolContext && prevProps.toolContext && (
            this.props.toolName !== prevProps.toolName ||
            this.props.toolContext.type !== prevProps.toolContext.type ||
            (this.props.toolContext.data && prevProps.toolContext.data && this.props.toolContext.data.id !== prevProps.toolContext.data.id)
          )) {
            this.props.close(prevProps.toolName, prevProps.toolContext)
          }

          // open current tool
          this.open()
        }
      }
    })
  }

  open() {
    this.pending = makeCancelable(
      this.props.open(this.props.toolName, this.props.toolContext)
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

      let app
      if (constants.TOOL_ADMINISTRATION === this.props.toolContext.type) {
        app = getAdminTool(this.props.toolName)
      } else {
        app = getTool(this.props.toolName)
      }

      this.pendingApp = makeCancelable(app)

      this.pendingApp.promise
        .then(
          (resolved) => {
            if (resolved.default) {
              this.setState({
                appLoaded: true,
                // I build the store here because if I do it in the render()
                // it will be called many times and will cause multiple mount/unmount of the app
                app: withReducer(this.props.toolName, resolved.default.store)(Tool),
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

    // only request close on tools effectively opened
    if (this.props.toolName && this.props.loaded && !this.props.notFound && !this.props.accessDenied) {
      this.props.close(this.props.toolName, this.props.toolContext)
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
  toolName: T.string.isRequired,
  toolContext: T.shape({
    type: T.string.isRequired,
    url: T.oneOfType([T.array, T.string]),
    data: T.object
  }).isRequired,
  loaded: T.bool.isRequired,
  notFound: T.bool.isRequired,
  accessDenied: T.bool.isRequired,
  open: T.func.isRequired,
  close: T.func.isRequired
}

ToolMain.defaultProps = {
  path: ''
}

export {
  ToolMain
}
