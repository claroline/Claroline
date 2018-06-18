import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/core/translation'

import {FormContainer} from '#/main/core/data/form/containers/form'
import {actions as formActions} from '#/main/core/data/form/actions'

import {ResourceType} from '#/main/core/resource/components/type'
import {constants} from '#/main/core/resource/constants'

const ResourceFormComponent = (props) =>
  <FormContainer
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
          }, /*{
            name: 'display.closable',
            label: trans('resource_closable', {}, 'resource'),
            type: 'boolean'
          }, */{
            name: 'display.closeTarget',
            label: trans('resource_close_target', {}, 'resource'),
            type: 'choice',
            required: true,
            options: {
              noEmpty: true,
              condensed: true,
              choices: constants.RESOURCE_CLOSE_TARGETS
            }
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
            calculated: (node) => node.restrictions.enableDates || !isEmpty(node.restrictions.dates),
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
                displayed: (node) => node.restrictions.enableDates || !isEmpty(node.restrictions.dates),
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
            calculated: (node) => node.restrictions.enableCode || !!node.restrictions.code,
            onChange: activated => {
              if (!activated) {
                props.updateProp('restrictions.code', null)
              }
            },
            linked: [
              {
                name: 'restrictions.code',
                label: trans('access_code'),
                displayed: (node) => node.restrictions.enableCode || !!node.restrictions.code,
                type: 'password',
                required: true
              }
            ]
          }, {
            name: 'restrictions.enableIps',
            label: trans('resource_access_ips', {}, 'resource'),
            type: 'boolean',
            calculated: (node) => node.restrictions.enableIps || !isEmpty(node.restrictions.allowedIps),
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
                displayed: (node) => node.restrictions.enableIps || !isEmpty(node.restrictions.allowedIps),
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
  </FormContainer>

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
