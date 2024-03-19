import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {selectors} from '#/main/core/tools/resources/store'
import {ToolPage} from '#/main/core/tool/containers/page'

import {ResourceList} from '#/main/core/resource/components/list'

const ResourcesTrash = (props) =>
  <ToolPage
    subtitle={trans('trash')}
  >
    <ResourceList
      className="my-3"
      path={props.path}
      name={selectors.STORE_NAME+ '.trash'}
      url={['apiv2_resource_workspace_removed_list', {
        workspace: props.contextId
      }]}
    />
  </ToolPage>

ResourcesTrash.propTypes = {
  path: T.string.isRequired,
  contextId: T.string.isRequired
}

export {
  ResourcesTrash
}
