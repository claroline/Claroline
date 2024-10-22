import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {MODAL_BUTTON} from '#/main/app/buttons'

import {constants as toolConstants, selectors as toolSelectors} from '#/main/core/tool'
import {ToolEditorActions} from '#/main/core/tool/editor'
import {MODAL_USER_DISABLE_INACTIVE} from '#/main/community/tools/community/user/modals/disable-inactive'

const CommunityEditorActions = () => {
  const contextType = useSelector(toolSelectors.contextType)

  return (
    <ToolEditorActions
      actions={[
        {
          title: trans('disable_inactive_users', {}, 'community'),
          help: trans('disable_inactive_users_help', {}, 'editor'),
          managerOnly: true,
          displayed: toolConstants.TOOL_DESKTOP === contextType,
          dangerous: true,
          action: {
            name: 'disable-inactive',
            type: MODAL_BUTTON,
            label: trans('disable', {}, 'actions'),
            modal: [MODAL_USER_DISABLE_INACTIVE]
          }
        }
      ]}
    />
  )
}

export {
  CommunityEditorActions
}
