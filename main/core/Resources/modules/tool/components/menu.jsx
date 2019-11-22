import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'

import {Await} from '#/main/app/components/await'

import {constants} from '#/main/core/tool/constants'
import {getTool} from '#/main/core/tools'
import {getTool as getAdminTool} from '#/main/core/administration'

const ToolMenu = props => {
  if (props.name && props.loaded) {
    let app
    if (constants.TOOL_ADMINISTRATION === props.contextType) {
      app = getAdminTool(props.name)
    } else {
      app = getTool(props.name)
    }

    return (
      <Await
        for={app}
        then={(module) => {
          if (module.default.menu) {
            return createElement(module.default.menu, {
              path: props.path,
              opened: props.opened,
              toggle: props.toggle,
              autoClose: props.autoClose
            })
          }

          return null
        }}
      />
    )
  }

  return null
}

ToolMenu.propTypes = {
  path: T.string,
  name: T.string,
  contextType: T.string,
  loaded: T.bool.isRequired,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  autoClose: T.func.isRequired
}

export {
  ToolMenu
}
