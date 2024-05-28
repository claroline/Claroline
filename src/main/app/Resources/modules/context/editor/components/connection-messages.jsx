import React from 'react'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'

const ContextEditorConnectionMessages = (props) =>
  <EditorPage
    title={trans('connection_messages')}
    help={trans('Affichez un ou plusieurs messages dans une modale à vos utilisateurs lorsqu\'ils ouvrent le contexte.')}
  >

  </EditorPage>

export {
  ContextEditorConnectionMessages
}
