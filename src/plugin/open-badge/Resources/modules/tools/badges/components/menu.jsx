import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {constants as toolConstants} from '#/main/core/tool/constants'
import {ToolMenu} from '#/main/core/tool/containers/menu'

const BadgeMenu = (props) =>
  <ToolMenu
    actions={[
      {
        name: 'my-badges',
        label: trans('my_badges', {}, 'badge'),
        target: props.path+'/my-badges',
        type: LINK_BUTTON,
        displayed: props.contextType !== toolConstants.TOOL_WORKSPACE || !get(props.workspace, 'meta.model')
      }, {
        name: 'all-badges',
        label: trans('all_badges', {}, 'badge'),
        target: props.path+'/badges',
        type: LINK_BUTTON
      }
    ]}
  />

BadgeMenu.propTypes = {
  path: T.string,
  contextType: T.string,
  workspace: T.object,
}

export {
  BadgeMenu
}
