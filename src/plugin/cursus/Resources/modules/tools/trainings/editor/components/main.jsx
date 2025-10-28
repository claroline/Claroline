import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {ToolEditor, selectors} from '#/main/core/tool/editor'

import {TrainingsEditorArchives} from '#/plugin/cursus/tools/trainings/editor/components/archives'

const TrainingsEditor = (props) => {
  const contextType = useSelector(selectors.contextType)

  return (
    <ToolEditor
      pages={[
        {
          name: 'archive',
          title: trans('archives'),
          help: trans('archived_trainings_help', {}, 'cursus'),
          disabled: 'desktop' !== contextType,
          render: () => (
            <TrainingsEditorArchives
              path={props.path}
            />
          )
        }
      ]}
    />
  )
}

TrainingsEditor.propTypes = {
  path: T.string.isRequired
}

export {
  TrainingsEditor
}
