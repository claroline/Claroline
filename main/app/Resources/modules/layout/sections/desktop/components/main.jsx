import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'
import {Await} from '#/main/app/components/await'
import {ContentLoader} from '#/main/app/content/components/loader'

import {ToolMain} from '#/main/core/tool/containers/main'

const DesktopMain = (props) =>
  <Await
    for={props.open(props.loaded)}
    placeholder={
      <ContentLoader
        size="lg"
        description="Nous chargeons votre bureau..."
      />
    }
    then={() => (
      <Routes
        path="/desktop"
        routes={[
          {
            path: '/:toolName',
            onEnter: (params = {}) => {
              if (-1 !== props.tools.findIndex(tool => tool.name === params.toolName)) {
                // tool is enabled for the desktop
                props.openTool(params.toolName)
              } else {
                // tool is disabled (or does not exist) for the desktop
                // let's go to the default opening of the desktop
                props.history.replace('/desktop')
              }
            },
            component: ToolMain
          }
        ]}
        redirect={[
          {from: '/', exact: true, to: `/${props.defaultOpening}`, disabled: !props.defaultOpening}
        ]}
      />
    )}
  />

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
