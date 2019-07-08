import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'

import {Await} from '#/main/app/components/await'

const ToolMenu = props => {
  if (props.name && props.loaded) {
    return (
      <Await
        for={props.getApp(props.name)}
        then={(module) => {
          if (module.default.menu) {
            return createElement(module.default.menu, {
              path: props.path,
              opened: props.opened,
              toggle: props.toggle
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
  loaded: T.bool.isRequired,
  getApp: T.func.isRequired,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired
}

export {
  ToolMenu
}
