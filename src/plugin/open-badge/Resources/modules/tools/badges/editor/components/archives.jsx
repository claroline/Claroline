import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {EditorPage} from '#/main/app/editor'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {BadgeList} from '#/plugin/open-badge/badge/components/list'
import {selectors} from '#/plugin/open-badge/tools/badges/editor/store/selectors'

const BadgesEditorArchives = () => {
  const contextId = useSelector(toolSelectors.contextId)
  const toolPath = useSelector(toolSelectors.path)

  return (
    <EditorPage
      title={trans('archives')}
      help={trans('Retrouvez et gérez tous les badges archivés.')}
      managerOnly={true}
    >
      <BadgeList
        path={toolPath}
        url={['apiv2_badge_archive_list', {contextId: contextId}]}
        name={selectors.ARCHIVES_LIST_NAME}
      />
    </EditorPage>
  )
}

export {
  BadgesEditorArchives
}
