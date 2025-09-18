import React, {useContext} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'

import {EditorPage} from '#/main/app/editor/components/page'
import {EditorContext} from '#/main/app/editor/context'
import {constants, ActionTypes, PromisedActionTypes} from '#/main/app/action'
import {ActionMenu} from '#/main/app/action/components/menu'

const EditorActions = ({
  actions = []
}) => {
  const editorDef = useContext(EditorContext)

  return (
    <EditorPage
      title={trans('advanced_actions', {}, 'actions')}
    >
      <ActionMenu
        set={constants.ACTION_SET_ADVANCED}
        manager={editorDef.canAdministrate}
        actions={actions}
      />
    </EditorPage>
  )
}

EditorActions.propTypes = {
  actions: T.oneOfType([
    // a regular array of actions
    T.arrayOf(T.shape(
      ActionTypes.propTypes
    )),
    // a promise that will resolve a list of actions
    T.shape(
      PromisedActionTypes.propTypes
    )
  ])
}

export {
  EditorActions
}
