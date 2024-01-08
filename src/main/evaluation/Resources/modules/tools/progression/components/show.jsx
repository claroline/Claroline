import React from 'react'

import {trans} from '#/main/app/intl'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {EvaluationUser} from '#/main/evaluation/tools/evaluation/containers/user'

const ProgressionShow = (props) =>
  <ToolPage
    path={[
      {
        type: LINK_BUTTON,
        label: trans('my_progression'),
        target: ''
      }
    ]}
    subtitle={trans('my_progression')}
  >
    <EvaluationUser
      userId={props.currentUserId}
      workspaceId={props.contextId}
    />
  </ToolPage>

ProgressionShow.propTypes = {

}

export {
  ProgressionShow
}
