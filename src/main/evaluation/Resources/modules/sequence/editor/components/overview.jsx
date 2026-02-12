import React from 'react'
import {useSelector} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'

import {selectors} from '#/main/evaluation/sequence/editor/store'
import {EditorOverview} from '#/main/app/editor/components/overview'

const SequenceEditorOverview = () => {
  const workspace = useSelector(selectors.workspace)
  const sequence = useSelector(selectors.data)

  return (
    <EditorOverview
      meta={{
        id: get(sequence, 'id'),
        updatedAt: get(sequence, 'meta.updatedAt')
      }}
      definition={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'poster',
              label: trans('poster'),
              type: 'poster',
              hideLabel: true
            }, {
              name: 'name',
              label: trans('name'),
              type: 'string',
              required: true
            }, {
              name: 'code',
              label: trans('code'),
              type: 'string',
              required: true
            }, {
              name: 'meta.published',
              type: 'boolean',
              label: trans('publish_sequence', {}, 'evaluation'),
              help: trans('publish_sequence_help', {}, 'evaluation')
            }
          ]
        }, {
          title: trans('further_information'),
          description: trans('further_information_help'),
          primary: true,
          fields: [
            {
              name: 'meta.description',
              label: trans('description_short'),
              help: trans('description_short_help', {}, 'resource'),
              type: 'string',
              recommended: true,
              options: {
                long: true,
                minRows: 2
              }
            }, {
              name: 'meta.descriptionHtml',
              label: trans('description_long'),
              type: 'html',
              help: trans('description_long_help', {}, 'resource'),
              options: {
                workspace: workspace
              }
            }, {
              name: 'tags',
              label: trans('tags'),
              type: 'tag'
            }
          ]
        }
      ]}
    />
  )
}

export {
  SequenceEditorOverview
}
