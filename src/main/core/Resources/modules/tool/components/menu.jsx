import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Toolbar} from '#/main/app/action'
import {PageMenu} from '#/main/app/page/components/menu'

import {getActions} from '#/main/core/tool/utils'

const ToolMenu = (props) =>
  <PageMenu actions={props.actions}>
    <Toolbar
      className="nav nav-underline text-shrink-0"
      buttonName="nav-link"
      toolbar="configure more"
      tooltip="bottom"
      actions={getActions(props.toolData, props.currentContext, {
        update: props.reload
      }, props.path)}
    />
  </PageMenu>

ToolMenu.propTypes = {
  path: T.string.isRequired,
  currentContext: T.shape({
    type: T.oneOf(['administration', 'desktop', 'workspace', 'account', 'public']),
    data: T.object
  }).isRequired,
  toolData: T.shape({
    icon: T.string,
    display: T.shape({
      showIcon: T.bool,
      fullscreen: T.bool
    }),
    poster: T.string,
    permissions: T.object.isRequired
  }),

  reload: T.func.isRequired
}

export {
  ToolMenu
}
