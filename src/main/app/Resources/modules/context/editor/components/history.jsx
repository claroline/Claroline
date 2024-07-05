import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'

import {LogOperationalList} from '#/main/log/components/operational-list'
import {selectors} from '#/main/app/context/store'

const ContextEditorHistory = () => {
  const contextName = useSelector(selectors.type)
  const contextId = useSelector(selectors.id)

  return (
    <EditorPage
      title={trans('history')}
      help={trans('Retrouvez toutes les modifications effectuées sur vos contenus.')}
    >
      <LogOperationalList
        autoload={!!contextName}
        url={['apiv2_logs_operational', {context: contextName, contextId: contextId}]}
      />
    </EditorPage>
  )
}

export {
  ContextEditorHistory
}
