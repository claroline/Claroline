import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl'
import {ToolEditor} from '#/main/core/tool/editor/containers/main'

import {TrainingsEditorArchive} from '#/plugin/cursus/tools/trainings/editor/components/list'

const TrainingsEditor = (props) =>
  <ToolEditor
    pages={[
      {
        name: 'archive',
        title: trans('archived_trainings', {}, 'cursus'),
        help: trans('archived_trainings_help', {}, 'cursus'),
        render: () => (
          <TrainingsEditorArchive
            path={props.path}
          />
        )
      }
    ]}
  />

TrainingsEditor.propTypes = {
  path: T.string.isRequired
}

export {
  TrainingsEditor
}
