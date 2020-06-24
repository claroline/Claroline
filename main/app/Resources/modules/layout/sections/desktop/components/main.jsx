import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {ContentLoader} from '#/main/app/content/components/loader'
import {ToolMain} from '#/main/core/tool/containers/main'

class DesktopMain extends Component {
  componentDidMount() {
    if (!this.props.loaded) {
      this.props.open()
    }
  }

  componentDidUpdate(prevProps) {
    if (!this.props.loaded && prevProps.loaded) {
      this.props.open()
    }
  }

  render() {
    if (!this.props.loaded) {
      return (
        <ContentLoader
          size="lg"
          description={trans('loading', {}, 'desktop')}
        />
      )
    }

    return (
      <Routes
        path="/desktop"
        routes={[
          {
            path: '/:toolName',
            onEnter: (params = {}) => {
              if (-1 !== this.props.tools.findIndex(tool => tool.name === params.toolName)) {
                // tool is enabled for the desktop
                this.props.openTool(params.toolName)
              } else if (0 !== this.props.tools.length) {
                // tool is disabled (or does not exist) for the desktop
                // let's go to the default opening of the desktop
                this.props.history.replace('/desktop')
              } else {
                // user has access to no desktop tool send him back to home
                this.props.history.replace('/')
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
}

DesktopMain.propTypes = {
  history: T.shape({
    replace: T.func.isRequired
  }).isRequired,
  loaded: T.bool.isRequired,
  defaultOpening: T.string,
  tools: T.arrayOf(T.shape({

  })),
  open: T.func.isRequired,
  openTool: T.func.isRequired
}

DesktopMain.defaultProps = {
  tools: []
}

export {
  DesktopMain
}
