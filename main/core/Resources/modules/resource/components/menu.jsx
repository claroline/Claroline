import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'

import {Await} from '#/main/app/components/await'
import {getResource} from '#/main/core/resources'

const ResourceMenu = props => {
  if (props.resourceType && props.loaded) {
    return (
      <Await
        for={getResource(props.resourceType)}
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

ResourceMenu.propTypes = {
  path: T.string.isRequired,
  resourceId: T.string,
  resourceType: T.string,
  loaded: T.bool.isRequired,

  // from menu
  opened: T.bool.isRequired,
  toggle: T.func.isRequired
}

export {
  ResourceMenu
}
