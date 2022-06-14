import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'

import {FormData} from '#/main/app/content/form/containers/data'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {ResourceIcon} from '#/main/core/resource/components/icon'

const restrictedByDates = (node) => get(node, 'restrictions.enableDates') || !isEmpty(get(node, 'restrictions.dates'))
const restrictedByCode = (node) => get(node, 'restrictions.enableCode') || !!get(node, 'restrictions.code')
const restrictedByIps = (node) => get(node, 'restrictions.enableIps') || !isEmpty(get(node, 'restrictions.allowedIps'))

const ResourceFormComponent = (props) =>
  <FormData
    level={props.level}
    name={props.name}
    dataPart={props.dataPart}
    meta={props.meta}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'meta.type',
            label: trans('type'),
            type: 'type',
            hideLabel: true,
            calculated: (resourceNode) => ({
              icon: <ResourceIcon mimeType={resourceNode.meta.mimeType} />,
              name: trans(resourceNode.meta.type, {}, 'resource'),
              description: trans(`${resourceNode.meta.type}_desc`, {}, 'resource')
            })
          }, {
            name: 'name',
            label: trans('name'),
            type: 'string',
            required: true
          }
        ]
      }, {
        className: 'embedded-form-section',
        title: trans('custom'),
        primary: true,
        displayed: !!props.children,
        render: () => props.children
      }, {
        icon: 'fa fa-fw fa-info',
        title: trans('information'),
        fields: [
          {
            name: 'meta.description',
            label: trans('description'),
            type: 'string',
            options: {
              long: true
            }
          }, {
            name: 'meta.published',
            label: trans('publish', {}, 'actions'),
            type: 'boolean'
          }
        ]
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
            name: 'display.showIcon',
            label: trans('resource_showIcon', {}, 'resource'),
            help: trans('resource_showIcon_help', {}, 'resource'),
            type: 'boolean'
          }, {
            name: 'display.showTitle',
            label: trans('show_title'),
            type: 'boolean'
          }, {
            name: 'display.fullscreen',
            label: trans('resource_fullscreen', {}, 'resource'),
            type: 'boolean'
          }
        ]
      }, {
        icon: 'fa fa-fw fa-key',
        title: trans('access_restrictions'),
        fields: [
          {
            name: 'restrictions.hidden',
            type: 'boolean',
            label: trans('restrict_hidden'),
            help: trans('restrict_hidden_help')
          }, {
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
          }, {
            name: 'restrictions.enableIps',
            label: trans('restrict_by_ips'),
            type: 'boolean',
            calculated: restrictedByIps,
            onChange: activated => {
              if (!activated) {
                props.updateProp('restrictions.allowedIps', [])
              }
            },
            linked: [
              {
                name: 'restrictions.allowedIps',
                label: trans('allowed_ips'),
                type: 'collection',
                required: true,
                displayed: restrictedByIps,
                options: {
                  type: 'ip',
                  placeholder: trans('no_allowed_ip'),
                  button: trans('add_ip')
                }
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

ResourceFormComponent.propTypes = {
  level: T.number,
  name: T.string.isRequired,
  dataPart: T.string,
  meta: T.bool,
  children: T.any,

  // from redux
  updateProp: T.func.isRequired
}

ResourceFormComponent.defaultProps = {
  level: 3,
  meta: true
}

const ResourceForm = connect(
  null,
  (dispatch, ownProps) =>({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp(ownProps.name, propName, propValue))
    }
  })
)(ResourceFormComponent)

export {
  ResourceForm
}
