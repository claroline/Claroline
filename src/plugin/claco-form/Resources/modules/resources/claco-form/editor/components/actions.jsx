import React from 'react'
import {useDispatch, useSelector} from 'react-redux'
import {trans} from '#/main/app/intl'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ResourceEditorActions} from '#/main/core/resource/editor'

import {actions, selectors} from '#/plugin/claco-form/resources/claco-form/editor/store'

const ClacoFormEditorActions = () => {
  const dispatch = useDispatch()
  const clacoForm = useSelector(selectors.clacoForm)

  return (
    <ResourceEditorActions
      actions={[
        {
          title: trans('Assigner les catégories aux fiches'),
          help: trans('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'),
          action: {
            label: trans('Assigner', {}, 'actions'),
            type: CALLBACK_BUTTON,
            callback: () => dispatch(actions.assignCategories(clacoForm))
          }
        },
      ]}
    />
  )
}

export {
  ClacoFormEditorActions
}
