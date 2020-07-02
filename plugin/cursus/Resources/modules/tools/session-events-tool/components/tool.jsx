import React from 'react'

import {trans} from '#/main/app/intl/translation'

import {ToolPage} from '#/main/core/tool/containers/page'

const SessionEventsTool = () =>
  <ToolPage>
    <div className="alert alert-warning">
      {trans('tool_work_in_progress')}
    </div>
  </ToolPage>


export {
  SessionEventsTool
}