import React, {useEffect} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolEditor} from '#/main/core/tool/editor/containers/main'
import {constants as toolConstants} from '#/main/core/tool/constants'

import {EditorProfile} from '#/main/community/tools/community/editor/containers/profile'
import {EditorParameters} from '#/main/community/tools/community/editor/containers/parameters'

const CommunityEditor = (props) =>
  <ToolEditor
    menu={[
      {
        name: 'overview',
        label: trans('about'),
        type: LINK_BUTTON,
        target: props.path+'/edit',
        exact: true
      }, {
        name: 'profile',
        type: LINK_BUTTON,
        label: trans('user_profile'),
        target: `${props.path}/edit/profile`,
        displayed: props.contextType === toolConstants.TOOL_DESKTOP
      }
    ]}
    pages={[
      {
        path: '/',
        exact: true,
        component: EditorParameters
      }, {
        path: '/profile',
        component: EditorProfile,
        disabled: props.contextType !== toolConstants.TOOL_DESKTOP
      }
    ]}
  />

CommunityEditor.propTypes = {
  path: T.string.isRequired,
  contextType: T.string.isRequired,
  contextId: T.string,
  /*parameters: T.object,
  profile: T.array,*/
  updateProp: T.func.isRequired,
  /*load: T.func.isRequired*/
}

export {
  CommunityEditor
}
