import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {actions as formActions, selectors as formSelect} from '#/main/app/content/form/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {Badge as BadgeTypes} from '#/plugin/open-badge/prop-types'
import {selectors}  from '#/plugin/open-badge/tools/badges/store/selectors'

const isAutoIssuing = (badge) => badge._autoIssuing || !isEmpty(badge.rules)

const BadgeFormComponent = (props) =>
  <FormData
    {...props}
    name={selectors.STORE_NAME +'.badges.current'}
    buttons={true}
    target={(badge, isNew) => isNew ?
      ['apiv2_badge-class_create'] :
      ['apiv2_badge-class_update', {id: badge.id}]
    }
    cancel={{
      type: LINK_BUTTON,
      exact: true,
      target: `${props.path}/badges/${!props.new ? props.badge.id : ''}`
    }}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'name',
            type: 'string',
            label: trans('name'),
            required: true
          }, {
            name: 'image',
            type: 'image',
            label: trans('image'),
            required: true
          }
        ]
      }, {
        icon: 'fa fa-fw fa-info',
        title: trans('information'),
        fields: [
          {
            name: 'description',
            label: trans('description'),
            type: 'html'
          }, {
            name: 'tags',
            label: trans('tags'),
            type: 'tag'
          }, {
            name: 'issuer',
            type: 'organization',
            label: trans('issuer', {}, 'badge'),
            displayed: 'workspace' !== props.currentContext.type
          }, {
            name: 'workspace',
            type: 'workspace',
            label: trans('workspace'),
            displayed: 'workspace' !== props.currentContext.type
          }
        ]
      }, {
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        fields: [
          {
            name: 'color',
            label: trans('color'),
            type: 'color'
          }, {
            name: 'template',
            label: trans('badge_certificate', {}, 'template'),
            type: 'template',
            options: {
              picker: {
                filters: [
                  {property: 'typeName', value: 'badge_certificate', locked: true}
                ]
              }
            }
          }
        ]
      }, {
        id: 'restrictions',
        icon: 'fa fa-fw fa-key',
        title: trans('access_restrictions'),
        fields: [
          {
            name: 'meta.enabled',
            type: 'boolean',
            label: trans('disable'),
            calculated: (badge) => !get(badge, 'meta.enabled', false),
            onChange: (disabled) => props.updateProp('meta.enabled', !disabled)
          }, {
            name: '_restrictDuration',
            type: 'boolean',
            label: trans('restrict_duration', {}, 'badge'),
            calculated: (badge) => badge._restrictDuration || !!badge.duration,
            onChange: (enabled) => {
              if (!enabled) {
                props.updateProp('duration', null)
              }
            },
            linked: [
              {
                name: 'duration',
                type: 'number',
                label: trans('duration'),
                required: true,
                displayed: (badge) => badge._restrictDuration || !!badge.duration,
                options: {
                  unit: trans('days')
                }
              }
            ]
          }, {
            name: 'restrictions.hideRecipients',
            type: 'boolean',
            label: trans('hide_recipients', {}, 'badge')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-certificate',
        title: trans('award_rules', {}, 'badge'),
        fields: [
          {
            name: 'criteria',
            label: trans('criteria', {}, 'badge'),
            type: 'html'
          }, {
            name: 'issuingPeer',
            type: 'boolean',
            label: trans('enable_manual_issuing', {}, 'badge')
          }, {
            name: '_autoIssuing',
            type: 'boolean',
            label: trans('enable_auto_issuing', {}, 'badge'),
            help: [
              trans('enable_auto_issuing_help', {}, 'badge'),
              trans('enable_auto_issuing_help_manual', {}, 'badge')
            ],
            calculated: isAutoIssuing,
            onChange: (enabled) => {
              if (!enabled) {
                props.updateProp('rules', [])
              }
            },
            linked: [
              {
                name: 'rules',
                label: trans('rules', {}, 'badge'),
                type: 'collection',
                displayed: isAutoIssuing,
                required: true,
                options: {
                  type: 'rule',
                  placeholder: trans('no_rule', {}, 'badge'),
                  button: trans('add_rule', {}, 'badge')
                }
              }
            ]
          }
        ]
      }
    ]}
  />

BadgeFormComponent.propTypes = {
  path: T.string.isRequired,
  currentContext: T.object.isRequired,
  new: T.bool.isRequired,
  badge: T.shape(
    BadgeTypes.propTypes
  ),
  updateProp: T.func.isRequired
}

const BadgeForm = connect(
  (state) => ({
    path: toolSelectors.path(state),
    currentContext: toolSelectors.context(state),
    new: formSelect.isNew(formSelect.form(state, selectors.STORE_NAME + '.badges.current')),
    badge: formSelect.data(formSelect.form(state, selectors.STORE_NAME + '.badges.current'))
  }),
  (dispatch) =>({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp(selectors.STORE_NAME +'.badges.current', propName, propValue))
    }
  })
)(BadgeFormComponent)

export {
  BadgeForm
}
