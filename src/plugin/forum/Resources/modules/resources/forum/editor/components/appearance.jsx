import React from 'react'

import {trans} from '#/main/app/intl'
import {ResourceEditorAppearance} from '#/main/core/resource/editor/components/appearance'

import DISPLAY_MODES from '#/main/app/content/list/modes'

const ForumEditorAppearance = () =>
  <ResourceEditorAppearance
    definition={[
      {
        //icon: 'fa fa-fw fa-desktop',
        title: trans('subjects', {}, 'forum'),
        subtitle: trans('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum porta dolor orci, ac venenatis sem fermentum nec.', {}, 'forum'),
        primary: true,
        fields: [
          {
            name: 'resource.display.subjectDataList',
            type: 'choice',
            label: trans('subjects_list_display', {}, 'forum'),
            options: {
              noEmpty: true,
              inline: false,
              choices: Object.keys(DISPLAY_MODES).reduce((acc, displayMode) => Object.assign(acc, {
                [displayMode]: DISPLAY_MODES[displayMode].label
              }), {})
            }
          }, {
            name: 'resource.display.messageOrder',
            type: 'choice',
            label: trans('message_order', {}, 'forum'),
            options: {
              noEmpty: true,
              choices: {
                ASC: trans('from_older_to_newer', {}, 'forum'),
                DESC: trans('from_newer_to_older', {}, 'forum')
              }
            }
          }
        ]
      }, {
        title: trans('comments'),
        subtitle: trans('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum porta dolor orci, ac venenatis sem fermentum nec.', {}, 'forum'),
        primary: true,
        fields: [
          {
            name: 'resource.display.expandComments',
            type: 'boolean',
            label: trans('expand_comments', {}, 'forum')
          }
        ]
      }
    ]}
  />

export {
  ForumEditorAppearance
}
