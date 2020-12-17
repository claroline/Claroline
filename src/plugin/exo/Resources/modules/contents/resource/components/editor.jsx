import React from 'react'
import {connect} from 'react-redux'
import get from 'lodash/get'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors as resourceSelect} from '#/main/core/resource/store'

import {ItemEditor as ItemEditorTypes} from '#/plugin/exo/items/prop-types'

const authorizedTypes = [
  'claro_slideshow',
  'claroline_scorm',
  'file',
  'icap_blog',
  'icap_lesson',
  'icap_wiki',
  'text'
]

const ResourceEditorComponent = props =>
  <FormData
    className="resource-item resource-editor"
    embedded={true}
    name={props.formName}
    dataPart={props.path}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'resource',
            label: trans('resource'),
            type: 'resource',
            required: true,
            help: trans('allowed_resource_types_info', {}, 'quiz'),
            options: {
              picker: {
                current: props.resourceNode && props.resourceNode.parent ? props.resourceNode.parent : null,
                root: null,
                filters: [{property: 'resourceType', value: ['directory'].concat(authorizedTypes), locked: true}]
              }
            },
            onChange: (resource) => {
              if (-1 === authorizedTypes.indexOf(get(resource, 'meta.type'))) {
                props.update('resource', null)
              }
            }
          }
        ]
      }
    ]}
  />

implementPropTypes(ResourceEditorComponent, ItemEditorTypes, {
  item: T.shape(

  ).isRequired,
  resourceNode: T.object
})

const ResourceEditor = connect(
  (state) => ({
    resourceNode: resourceSelect.resourceNode(state)
  })
)(ResourceEditorComponent)

export {
  ResourceEditor
}
