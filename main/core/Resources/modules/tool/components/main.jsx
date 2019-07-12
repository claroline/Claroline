import React, {createElement, Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {theme} from '#/main/app/config'
import {withReducer} from '#/main/app/store/components/withReducer'
import {Await} from '#/main/app/components/await'
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
  componentDidMount() {
    this.props.open(this.props.toolName, this.props.toolContext, this.props.path)
  }

  componentDidUpdate(prevProps) {
    if (this.props.toolName !== prevProps.toolName
      || this.props.toolContext.type !== prevProps.toolContext.type
      || get(this.props.toolContext, 'data.id') !== get(prevProps.toolContext, 'data.id')
    ) {
      if (prevProps.toolName) {
        this.props.close()
      }

      if (this.props.toolName) {
        this.props.open(this.props.toolName, this.props.toolContext, this.props.path)
      }
    }
  }

  componentWillUnmount() {
    this.props.close()
  }

  render() {
    let app
    if (constants.TOOL_ADMINISTRATION === this.props.toolContext.type) {
      app = getAdminTool(this.props.toolName)
    } else {
      app = getTool(this.props.toolName)
    }

    return (
      <Await
        for={app}
        placeholder={
          <ContentLoader
            size="lg"
            description="Nous chargeons votre outil"
          />
        }
        then={(module) => {
          if (module.default) {
            const ToolApp = withReducer(this.props.toolName, module.default.store)(Tool)

            return (
              <ToolApp
                loaded={this.props.loaded}
                styles={module.default.styles}
              >
                {module.default.component && createElement(module.default.component, {
                  path: this.props.path + '/' + this.props.toolName
                })}
              </ToolApp>
            )
          }

          return null
        }}
      />
    )
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

  // from store
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
