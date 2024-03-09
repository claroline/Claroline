import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {ToolMenu} from '#/main/core/tool/containers/menu'
import {LINK_BUTTON} from '#/main/app/buttons'

const ForumMenu = (props) =>
  <ToolMenu
    actions={[
      {
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-list-ul',
        label: trans('subjects', {}, 'forum'),
        target: `${props.path}/subjects`
      }
    ]}
  />

ForumMenu.propTypes = {
  path: T.string.isRequired
}

export {
  ForumMenu
}
