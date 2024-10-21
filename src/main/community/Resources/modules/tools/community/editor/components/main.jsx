import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {ToolEditor} from '#/main/core/tool/editor/containers/main'
import {constants as toolConstants} from '#/main/core/tool/constants'

import {EditorProfile} from '#/main/community/tools/community/editor/containers/profile'
import {EditorParameters} from '#/main/community/tools/community/editor/containers/parameters'
import {CommunityEditorActions} from '#/main/community/tools/community/editor/components/actions'
import {CommunityEditorReported} from '#/main/community/tools/community/editor/components/reported'

const CommunityEditor = (props) =>
  <ToolEditor
    defaultPage="overview"
    overviewPage={EditorParameters}
    actionsPage={CommunityEditorActions}
    pages={[
      {
        name: 'profile',
        title: trans('user_profile'),
        help: trans('Ajoutez des champs personnalisés pour enrichir le profil de vos utilisateurs.'),
        component: EditorProfile,
        disabled: props.contextType !== toolConstants.TOOL_DESKTOP
      }, {
        name: 'reported',
        title: trans('user_reported', {}, 'community'),
        help: trans('user_reported_help', {}, 'community'),
        component: CommunityEditorReported
      }
    ]}
  />

CommunityEditor.propTypes = {
  path: T.string.isRequired,
  contextType: T.string.isRequired,
  contextId: T.string
}

export {
  CommunityEditor
}
