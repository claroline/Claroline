import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {EditorPage} from '#/main/app/editor'
import {selectors as toolSelectors} from '#/main/core/tool'

import {ResourceList} from '#/main/core/resource/components/list'
import {selectors} from '#/main/core/tools/resources/editor/store/selectors'
import {constants as listConst} from '#/main/app/content/list'

const EditorArchives = () => {
  const contextId = useSelector(toolSelectors.contextId)
  const path = useSelector(toolSelectors.path)

  return (
    <EditorPage
      title={trans('archives', {}, 'resource')}
      help={trans('archives_desc', {}, 'resource')}
      managerOnly={true}
    >
      <ResourceList
        className="mb-5"
        path={path}
        name={selectors.ARCHIVES_LIST_NAME}
        url={['claro_resource_archive_list', {
          contextId: contextId
        }]}
        display={{
          current: listConst.DISPLAY_LIST
        }}
        customDefinition={[
          {
            name: 'parent',
            label: trans('directory', {}, 'resource'),
            type: 'resource'
          }
        ]}
      />
    </EditorPage>
  )
}

export {
  EditorArchives
}
