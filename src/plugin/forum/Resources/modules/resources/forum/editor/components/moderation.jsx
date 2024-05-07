import React from 'react'

import {trans} from '#/main/app/intl'
import {EditorPage} from '#/main/app/editor'

import {constants} from '#/plugin/forum/resources/forum/constants'

const ForumEditorModeration = (props) =>
  <EditorPage
    title={trans('moderation', {}, 'forum')}
    help={trans('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vestibulum porta dolor orci, ac venenatis sem fermentum nec.', {}, 'forum')}
    definition={[
      {
        icon: 'fa fa-fw fa-gavel',
        title: trans('moderation', {}, 'forum'),
        primary: true,
        hideTitle: true,
        fields: [
          {
            name: 'resource.moderation',
            type: 'choice',
            label: trans('moderation_type', {}, 'forum'),
            options: {
              noEmpty: true,
              choices: constants.MODERATION_MODES
            }
          }, {
            name: '_lockForum',
            label: trans('restrict_by_dates'),
            type: 'boolean',
            linked: [
              {
                name: 'resource.restrictions.lockDate',
                type: 'date',
                label: trans('date'),
                displayed: (formData) => formData._lockForum,
                required: true,
                options: {
                  time: true
                }
              }
            ]
          }
        ]
      }
    ]}
  />

export {
  ForumEditorModeration
}