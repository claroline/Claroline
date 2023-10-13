import React, {Component, createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {makeCancelable} from '#/main/app/api'
import {Routes} from '#/main/app/router'
import {ToolMain} from '#/main/core/tool/containers/main'
import {FooterMain} from '#/main/app/layout/footer/containers/main'
import {trans} from '#/main/app/intl'
import {ContentNotFound} from '#/main/app/content/components/not-found'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ContentForbidden} from '#/main/app/content/components/forbidden'

class ContextMain extends Component {
  componentDidMount() {
    if (this.props.name) {
      this.open()
    }
  }

  componentDidUpdate(prevProps) {
    let needOpen = false

    // loaded status has changed
    if (this.props.loaded !== prevProps.loaded && !this.props.loaded) {
      needOpen = true
    }

    // context has changed
    if (this.props.name && (this.props.name !== prevProps.name || prevProps.id !== this.props.id)) {
      needOpen = true

      // close previous context
      if (this.openQuery) {
        this.openQuery.cancel()
        this.openQuery = null
      }

      if (prevProps.loaded && !prevProps.notFound && isEmpty(prevProps.accessErrors)) {
        // the previous context was fully loaded, with no error, close it
        this.props.close(prevProps.name, prevProps.id)
      }
    }

    if (needOpen) {
      // (re)open the current context
      this.open()
    }
  }

  componentWillUnmount() {
    if (this.openQuery) {
      this.openQuery.cancel()
      this.openQuery = null
    }

    this.close()
  }

  open() {
    if (this.openQuery) {
      return
    }

    this.openQuery = makeCancelable(
      this.props.open(this.props.name, this.props.id)
    )

    this.openQuery.promise
      .then(
        () => this.openQuery = null,
        () => this.openQuery = null
      )
  }

  close() {
    if (this.openQuery) {
      this.openQuery.cancel()
      this.openQuery = null
    }

    // only request close on contexts effectively opened
    if (this.props.name && this.props.loaded && !this.props.notFound && isEmpty(this.props.accessErrors)) {
      this.props.close(this.props.name, this.props.id)
    }
  }

  render() {
    let CurrentComp
    if (this.props.notFound) {
      CurrentComp = this.props.notFoundPage ?
        createElement(this.props.notFoundPage) :
        <ContentNotFound
          size="lg"
          title={trans('not_found')}
          description={trans('not_found_desc')}
        />
    } else if (!this.props.loaded) {
      CurrentComp = this.props.loadingPage ?
        createElement(this.props.loadingPage) :
        <ContentLoader
          size="lg"
          description={trans('loading')}
        />
    } else if (!isEmpty(this.props.accessErrors)) {
      CurrentComp = this.props.loadingPage ?
        createElement(this.props.forbiddenPage) :
        <ContentForbidden
          size="lg"
          title={trans('access_forbidden')}
          description={trans('access_forbidden_help')}
        />
    } else {
      CurrentComp = (
        <Routes
          path={this.props.path}
          routes={[
            {
              path: '/:toolName',
              onEnter: (params = {}) => {
                if (-1 !== this.props.tools.findIndex(tool => tool.name === params.toolName)) {
                  // tool is enabled for the context
                  this.props.openTool(params.toolName)
                } else {
                  // tool is disabled (or does not exist) for the context
                  // let's go to the default opening of the context
                  this.props.history.replace(this.props.path)
                }
              },
              component: ToolMain
            }
          ]}
          redirect={[
            {from: '/', exact: true, to: `/${this.props.defaultOpening}`, disabled: !this.props.defaultOpening}
          ]}
        />
      )
    }

    return (
      <>
        {createElement(this.props.menu)}

        <div className="app-content" role="presentation">
          {CurrentComp}

          {this.props.footer && createElement(this.props.footer)}
        </div>
      </>
    )
  }
}

ContextMain.propTypes = {
  parent: T.string,

  // context info
  path: T.string.isRequired,
  id: T.string,
  name: T.string.isRequired,

  // context status
  loaded: T.bool.isRequired,
  notFound: T.bool.isRequired,
  accessErrors: T.object,
  // context params
  defaultOpening: T.string,
  tools: T.arrayOf(T.shape({

  })),
  // custom context components
  menu: T.elementType,
  footer: T.elementType,
  loadingPage: T.elementType,
  notFoundPage: T.elementType,
  forbiddenPage: T.elementType,

  open: T.func.isRequired,
  close: T.func.isRequired,
  openTool: T.func.isRequired,
  history: T.shape({
    replace: T.func.isRequired
  }).isRequired
}

ContextMain.defaultProps = {
  tools: [],
  footer: FooterMain
}

export {
  ContextMain
}
