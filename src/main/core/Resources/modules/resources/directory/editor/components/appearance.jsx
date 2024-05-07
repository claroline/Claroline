import React from 'react'
import {useSelector} from 'react-redux'

import {selectors as securitySelectors} from '#/main/app/security/store'
import {ListForm} from '#/main/app/content/list/parameters/containers/form'

import {selectors as resourceSelectors} from '#/main/core/resource'
import {selectors as editorSelectors} from '#/main/core/resource/editor'
import {ResourceEditorAppearance} from '#/main/core/resource/editor'
import resourcesSource from '#/main/core/data/sources/resources'

const DirectoryEditorAppearance = () => {
  const currentUser = useSelector(securitySelectors.currentUser)
  const workspace = useSelector(resourceSelectors.workspace)
  const directory = useSelector(editorSelectors.resource)

  return (
    <ResourceEditorAppearance>
      <ListForm
        level={3}
        name={editorSelectors.STORE_NAME}
        dataPart="resource.list"
        list={resourcesSource('workspace', workspace, {}, currentUser)}
        parameters={directory.list}
      />
    </ResourceEditorAppearance>
  )
}

export {
  DirectoryEditorAppearance
}
