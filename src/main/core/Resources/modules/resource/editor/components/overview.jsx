import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'

import {trans} from '#/main/app/intl'
import {FormContent} from '#/main/app/content/form/containers/content'

import {selectors} from '#/main/core/resource/store'
import {ResourceIcon} from '#/main/core/resource/components/icon'

const restrictedByDates = (node) => get(node, 'restrictions.enableDates') || !isEmpty(get(node, 'restrictions.dates'))
const restrictedByCode = (node) => get(node, 'restrictions.enableCode') || !!get(node, 'restrictions.code')

const EditorOverview = (props) =>
  <FormContent
    name={selectors.EDITOR_NAME}
    dataPart="resourceNode"
    autoFocus={true}
    definition={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
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
            label: trans('publish', {}, 'actions'),
            type: 'boolean',
            help: [
              trans('Publiez la ressource pour la rendre accessible à vos utilisateurs.', {}, 'resource'),
              trans('Temps que la ressource n\'est pas publiée, elle est uniquement accessible aux utilisateurs ayant la permission "Modifier".', {}, 'resource')
            ]
          }
        ]
      },{
        title: trans('advanced'),
        primary: true,
        fields: [
          {
            name: 'meta.description',
            label: trans('description'),
            type: 'string',
            options: {
              long: true
            }
          }, {
            name: 'tags',
            label: trans('tags'),
            type: 'tag'
          }
        ]
      }, {
        title: trans('custom'),
        primary: true,
        fill: true,
        displayed: !!props.customSection,
        render: () => props.customSection
      }, {
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        fields: [
          {
            name: 'poster',
            label: trans('poster'),
            type: 'image'
          }, {
            name: 'thumbnail',
            label: trans('thumbnail'),
            type: 'image'
          }, {
            name: 'restrictions.hidden',
            type: 'boolean',
            label: trans('restrict_hidden'),
            help: trans('restrict_hidden_help')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-key',
        title: trans('access_restrictions'),
        fields: [
          {
            name: 'restrictions.enableDates',
            label: trans('restrict_by_dates'),
            type: 'boolean',
            calculated: restrictedByDates,
            onChange: activated => {
              if (!activated) {
                props.updateProp('restrictions.dates', [])
              }
            },
            linked: [
              {
                name: 'restrictions.dates',
                type: 'date-range',
                label: trans('access_dates'),
                displayed: restrictedByDates,
                required: true,
                options: {
                  time: true
                }
              }
            ]
          }, {
            name: 'restrictions.enableCode',
            label: trans('restrict_by_code'),
            type: 'boolean',
            calculated: restrictedByCode,
            onChange: activated => {
              if (!activated) {
                props.updateProp('restrictions.code', '')
              }
            },
            linked: [
              {
                name: 'restrictions.code',
                label: trans('access_code'),
                displayed: restrictedByCode,
                type: 'password',
                required: true
              }
            ]
          }
        ]
      }, {
        icon: 'fa fa-fw fa-award',
        title: trans('evaluation'),
        fields: [
          {
            name: 'evaluation.estimatedDuration',
            label: trans('estimated_duration'),
            type: 'number',
            options: {
              unit: trans('minutes')
            }
          }, {
            name: 'evaluation.required',
            label: trans('require_resource', {}, 'resource'),
            type: 'boolean',
            help: trans('require_resource_help', {}, 'resource'),
            onChange: (required) => {
              if (!required) {
                props.updateProp('evaluation.evaluated', false)
              }
            },
            linked: [
              {
                name: 'evaluation.evaluated',
                label: trans('evaluate_resource', {}, 'resource'),
                type: 'boolean',
                help: trans('evaluate_resource_help', {}, 'resource'),
                displayed: (resource) => get(resource, 'evaluation.required', false)
              }
            ]
          }
        ]
      }, {
        icon: 'fa fa-fw fa-copyright',
        title: trans('authors_license'),
        fields: [
          {
            name: 'meta.authors',
            label: trans('authors'),
            type: 'string'
          }, {
            name: 'meta.license',
            label: trans('license'),
            type: 'string'
          }
        ]
      }
    ]}
  />

EditorOverview.propTypes = {

}

export {
  EditorOverview
}
