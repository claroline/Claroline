import React from 'react'
import {PropTypes as T} from 'prop-types'

import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolMenu} from '#/main/core/tool/containers/menu'

const ExampleMenu = (props) =>
  <ToolMenu
    actions={[
      {
        name: 'crud',
        type: LINK_BUTTON,
        label: 'Simple CRUD',
        target: props.path+'/crud'
      }, {
        name: 'forms',
        type: LINK_BUTTON,
        label: 'Forms',
        target: props.path+'/forms'
      }, {
        name: 'components',
        type: LINK_BUTTON,
        label: 'Components',
        target: props.path+'/components'
      }
    ]}
  />

ExampleMenu.propTypes = {
  path: T.string
}

export {
  ExampleMenu
}
