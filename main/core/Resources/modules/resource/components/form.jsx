import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/core/translation'

import {FormData} from '#/main/app/content/form/containers/data'
import {actions as formActions} from '#/main/app/content/form/store/actions'

import {ResourceType} from '#/main/core/resource/components/type'

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
            type: 'string',
            hideLabel: true,
            render: (resourceNode) => {
              const NodeType =
                <ResourceType
                  name={resourceNode.meta.type}
                  mimeType={resourceNode.meta.mimeType}
                />

              return NodeType
            }
          }, {
            name: 'name',
            label: trans('name'),
            type: 'string',
            required: true
          }
        ]
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
            label: trans('resource_not_published', {}, 'resource'),
            type: 'boolean',
            options: {
              labelChecked: trans('resource_published', {}, 'resource')
            }
          }, {
            name: 'meta.portal',
            label: trans('resource_portal_not_published', {}, 'resource'),
            type: 'boolean',
            options: {
              labelChecked: trans('resource_portal_published', {}, 'resource')
            }
          }
        ]
      }, {
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        fields: [
          {
            name: 'thumbnail',
            label: trans('thumbnail'),
            type: 'image'
          },
          {
            name: 'poster',
            label: trans('poster'),
            type: 'image',
            options: {
              ratio: '3:1'
            }
          }, {
            name: 'display.showIcon',
            label: trans('resource_showIcon', {}, 'resource'),
            help: trans('resource_showIcon_help', {}, 'resource'),
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
            label: trans('resource_access_code', {}, 'resource'),
            type: 'boolean',
            calculated: restrictedByCode,
            onChange: activated => {
              if (!activated) {
                props.updateProp('restrictions.code', null)
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
            label: trans('resource_access_ips', {}, 'resource'),
            type: 'boolean',
            calculated: restrictedByIps,
            onChange: activated => {
              if (!activated) {
                props.updateProp('restrictions.ips', [])
              }
            },
            linked: [
              {
                name: 'restrictions.allowedIps',
                label: trans('resource_allowed_ip'),
                type: 'ip',
                required: true,
                displayed: restrictedByIps,
                options: {
                  placeholder: trans('resource_no_allowed_ip', {}, 'resource'),
                  multiple: true
                }
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
  >
    {props.children}
  </FormData>

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
  (dispatch, ownProps) =>({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp(ownProps.name, propName, propValue))
    }
  })
)(ResourceFormComponent)

export {
  ResourceForm
}
