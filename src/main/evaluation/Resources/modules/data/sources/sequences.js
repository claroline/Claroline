import React from 'react'
import {trans} from '#/main/app/intl/translation'

import {DataMicro} from '#/main/app/data/components/micro'
import {route as workspaceRoute} from '#/main/core/workspace'
import {route as toolRoute} from '#/main/core/tool'

import {getActions, getDefaultAction} from '#/main/evaluation/sequence/utils'
import {SequenceCard} from '#/main/evaluation/sequence/components/card'

export default (contextType, contextData, refresher, currentUser) => {
  let basePath
  if ('workspace' === contextType) {
    basePath = workspaceRoute(contextData, 'progression')
  } else {
    basePath = toolRoute('progression')
  }

  return {
    primaryAction: (sequence) => getDefaultAction(sequence, refresher, basePath, currentUser),
    actions: (sequences) => getActions(sequences, refresher, basePath, currentUser),
    definition: [
      {
        name: 'name',
        type: 'string',
        label: trans('name'),
        displayed: true,
        primary: true,
        render: (sequence) => <DataMicro object={sequence} />
      }, {
        name: 'meta.description',
        type: 'string',
        label: trans('description'),
        sortable: false,
        options: {long: true}
      }, {
        name: 'code',
        type: 'string',
        label: trans('code')
      }, {
        name: 'meta.createdAt',
        label: trans('creation_date'),
        type: 'date',
        alias: 'createdAt',
        filterable: false,
        options: {time: true}
      }, {
        name: 'meta.updatedAt',
        label: trans('modification_date'),
        type: 'date',
        alias: 'updatedAt',
        displayed: true,
        filterable: false,
        options: {time: true}
      }, {
        name: 'meta.creator',
        label: trans('creator'),
        type: 'user',
        alias: 'creator',
        sortable: false
      }, {
        name: 'meta.published',
        label: trans('published'),
        type: 'boolean',
        alias: 'published'
      }, {
        name: 'tags',
        type: 'tag',
        label: trans('tags'),
        sortable: false,
        options: {
          objectClass: 'Claroline\\EvaluationBundle\\Entity\\Sequence'
        }
      }
    ],
    card: SequenceCard
  }
}
