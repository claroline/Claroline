import React from 'react'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {EditorActions} from '#/main/app/editor'
import {supportEvaluation} from '#/main/core/resource/utils'
import {selectors} from '#/main/core/resource/editor/store'

const ResourceEditorActions = (props) => {
  const editedNode = useSelector(selectors.resourceNode)

  return (
    <EditorActions
      actions={[
        {
          title: trans('Changer le propriétaire'),
          help: trans('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'),
          action: {
            label: trans('Transférer', {}, 'actions'),
            type: CALLBACK_BUTTON,
            callback: () => true
          },
          managerOnly: true
        }, {
          title: trans('Recalculer les évaluations'),
          help: trans('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'),
          displayed: supportEvaluation(editedNode),
          action: {
            label: trans('recalculate', {}, 'actions'),
            type: CALLBACK_BUTTON,
            callback: () => true
          }
        }, {
          title: trans('Purger les évaluations'),
          help: trans('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'),
          action: {
            label: trans('purge', {}, 'actions'),
            type: CALLBACK_BUTTON,
            callback: () => true
          },
          displayed: supportEvaluation(editedNode),
          dangerous: true,
          managerOnly: true
        }, {
          title: trans('Archiver la ressource'),
          help: trans('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'),
          action: {
            label: trans('archive', {}, 'actions'),
            type: CALLBACK_BUTTON,
            callback: () => true
          },
          dangerous: true,
          managerOnly: true
        }, {
          title: trans('Supprimer la ressource'),
          help: trans('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.'),
          action: {
            label: trans('delete', {}, 'actions'),
            type: CALLBACK_BUTTON,
            callback: () => true
          },
          dangerous: true,
          managerOnly: true
        }
      ].concat(props.actions || [])}
    />
  )
}

ResourceEditorActions.propTypes = EditorActions.propTypes
ResourceEditorActions.defaultProps = EditorActions.defaultProps

export {
  ResourceEditorActions
}
