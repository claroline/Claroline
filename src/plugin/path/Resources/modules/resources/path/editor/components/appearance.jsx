import React from 'react'

import {trans} from '#/main/app/intl'
import {ResourceEditorAppearance} from '#/main/core/resource/editor/components/appearance'

import {constants} from '#/plugin/path/resources/path/constants'

const PathEditorAppearance = () =>
  <ResourceEditorAppearance
    definition={[
      {
        name: 'numbering',
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        primary: true,
        hideTitle: true,
        fields: [
          {
            name: 'resource.display.numbering',
            type: 'choice',
            label: trans('path_numbering', {}, 'path'),
            required: true,
            options: {
              noEmpty: true,
              condensed: false,
              choices: constants.PATH_NUMBERINGS
            }
          }
        ]
      }
    ]}
  />

export {
  PathEditorAppearance
}
