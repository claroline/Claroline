import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'

import {Await} from '#/main/app/components/await'

import {getTool} from '#/main/core/tool/utils'

const ToolMenu = props => {
  if (props.name && props.loaded && !props.notFound) {
    return (
      <Await
        for={getTool(props.name, props.contextType)}
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
  notFound: T.bool.isRequired,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired,
  autoClose: T.func.isRequired
}

export {
  ToolMenu
}
