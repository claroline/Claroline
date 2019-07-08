import React, {createElement, Component, Fragment} from 'react'
import {PropTypes as T} from 'prop-types'

import {theme} from '#/main/app/config'
import {withReducer} from '#/main/app/store/components/withReducer'
import {Await} from '#/main/app/components/await'
import {ContentLoader} from '#/main/app/content/components/loader'

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
    this.props.open(this.props.toolName)
  }

  componentDidUpdate(prevProps) {
    if (this.props.toolName !== prevProps.toolName) {
      if (prevProps.toolName) {
        this.props.close()
      }

      if (this.props.toolName) {
        this.props.open(this.props.toolName)
      }
    }
  }

  componentWillUnmount() {
    this.props.close()
  }

  render() {
    const props = this.props

    return (
      <Await
        for={props.getApp(props.toolName)}
        placeholder={
          <ContentLoader
            size="lg"
            description="Nous chargeons votre outil"
          />
        }
        then={(module) => {
          if (module.default) {
            const ToolApp = withReducer(props.toolName, module.default.store)(Tool)

            return (
              <ToolApp
                loaded={props.loaded}
                styles={module.default.styles}
              >
                {module.default.component && createElement(module.default.component, {
                  path: props.path
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
  path: T.string.isRequired,
  toolName: T.string.isRequired,

  getApp: T.func.isRequired,
  loaded: T.bool.isRequired,
  open: T.func.isRequired,
  close: T.func.isRequired
}

export {
  ToolMain
}
