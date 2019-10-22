import React, {createElement, Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {theme} from '#/main/app/config'
import {withReducer} from '#/main/app/store/components/withReducer'
import {makeCancelable} from '#/main/app/api'
import {ContentLoader} from '#/main/app/content/components/loader'

import {constants} from '#/main/core/tool/constants'
import {getTool} from '#/main/core/tools'
import {getTool as getAdminTool} from '#/main/core/administration'

const Tool = props => {
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
      description="Nous chargeons votre outil"
    />
  )
}

Tool.propTypes = {
  loaded: T.bool.isRequired,
  styles: T.arrayOf(T.string),
  children: T.node
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
  }

  componentDidMount() {
    this.loadApp()
    if (!this.props.loaded) {
      this.props.open(this.props.toolName, this.props.toolContext)
    }
  }

  componentDidUpdate(prevProps) {
    if (this.props.toolName && this.props.toolName !== prevProps.toolName) {
      if (this.pending) {
        this.pending.cancel()
        this.pending = null
      }

      this.loadApp()
    }

    if (!this.props.loaded && this.props.loaded !== prevProps.loaded) {
      this.props.open(this.props.toolName, this.props.toolContext)
    }

    if (this.props.toolName && prevProps.toolName && this.props.toolContext && prevProps.toolContext && (
      this.props.toolName !== prevProps.toolName ||
      this.props.toolContext.type !== prevProps.toolContext.type ||
      (this.props.toolContext.data && prevProps.toolContext.data && this.props.toolContext.data.id !== prevProps.toolContext.data.id)
    )) {
      this.props.close(prevProps.toolName, prevProps.toolContext)
    }
  }

  loadApp() {
    if (!this.pending) {
      this.setState({appLoaded: false})

      let app
      if (constants.TOOL_ADMINISTRATION === this.props.toolContext.type) {
        app = getAdminTool(this.props.toolName)
      } else {
        app = getTool(this.props.toolName)
      }

      this.pending = makeCancelable(app)

      this.pending.promise
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
          () => {
            // TODO : properly handle error
          }
        )
        .then(
          () => this.pending = null,
          () => this.pending = null
        )
    }
  }

  componentWillUnmount() {
    if (this.pending) {
      this.pending.cancel()
      this.pending = null
    }
    this.props.close(this.props.toolName, this.props.toolContext)
  }

  render() {
    if (!this.state.appLoaded) {
      return (
        <ContentLoader
          size="lg"
          description="Nous chargeons votre outil"
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
  open: T.func.isRequired,
  close: T.func.isRequired
}

ToolMain.defaultProps = {
  path: ''
}

export {
  ToolMain
}
