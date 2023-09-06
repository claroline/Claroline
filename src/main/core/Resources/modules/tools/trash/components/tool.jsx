import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {selectors} from '#/main/core/tools/trash/store'
import {ToolPage} from '#/main/core/tool/containers/page'

import {ResourceList} from '#/main/core/resource/components/list'

const TrashTool = (props) =>
  <ToolPage
    subtitle={trans('trash')}
  >
    <ResourceList
      className="my-3"
      path={props.path}
      name={selectors.STORE_NAME + '.resources'}
      url={['apiv2_resource_workspace_removed_list', {
        workspace: props.workspace.id
      }]}
    />
  </ToolPage>

TrashTool.propTypes = {
  path: T.string.isRequired,
  workspace: T.shape({
    id: T.string.isRequired
  })
}

export {
  TrashTool
}
