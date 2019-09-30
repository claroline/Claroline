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

import {constants} from '#/plugin/open-badge/tools/badges/badge/constants'
import {selectors}  from '#/plugin/open-badge/tools/badges/store/selectors'

const isManualIssuing = (badge) => badge._manualIssuing || !isEmpty(badge.issuingMode)
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
      target: props.path + '/badges'
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
                displayed: (badge) => badge._restrictDuration || badge.duration
              }
            ]
          }
        ]
      }, {
        icon: 'fa fa-fw fa-certificate',
        title: trans('Règles d\'attribution', {}, 'badge'),
        fields: [
          {
            name: '_manualIssuing',
            type: 'boolean',
            label: trans('enable_manual_issuing', {}, 'badge'),
            help: trans('enable_manual_issuing_help', {}, 'badge'),
            calculated: isManualIssuing,
            onChange: (enabled) => {
              if (!enabled) {
                props.updateProp('issuingMode', [])
                props.updateProp('allowedUsers', [])
                props.updateProp('allowedGroups', [])
              }
            },
            linked: [
              {
                name: 'criteria',
                label: trans('criteria', {}, 'badge'),
                type: 'html',
                required: true,
                displayed: isManualIssuing
              }, {
                name: 'issuingMode',
                type: 'choice',
                label: trans('allowed_issuers', {}, 'badge'),
                required: true,
                displayed: isManualIssuing,
                options: {
                  choices: constants.ISSUING_MODES,
                  multiple: true,
                  inline: false
                }
              }, {
                name: 'allowedUsers',
                label: trans('users'),
                type: 'users',
                displayed: badge => isManualIssuing(badge) && (badge.issuingMode && badge.issuingMode.indexOf(constants.ISSUING_MODE_USER) > -1)
              }, {
                name: 'allowedGroups',
                label: trans('groups'),
                type: 'groups',
                displayed: badge => isManualIssuing(badge) && (badge.issuingMode && badge.issuingMode.indexOf(constants.ISSUING_MODE_GROUP) > -1)
              }
            ]
          }, {
            name: '_autoIssuing',
            type: 'boolean',
            label: trans('enable_auto_issuing', {}, 'badge'),
            help: isManualIssuing(props.badge) ? [
              trans('enable_auto_issuing_help', {}, 'badge'),
              trans('enable_auto_issuing_help_manual', {}, 'badge')
            ] : trans('enable_auto_issuing_help', {}, 'badge'),
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
                required: true,
                displayed: isAutoIssuing,
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
  badge: T.shape({
    // TODO : badge types
  }),
  updateProp: T.func.isRequired
}

const BadgeForm = connect(
  (state) => ({
    path: toolSelectors.path(state),
    currentContext: toolSelectors.context(state),
    new: formSelect.isNew(formSelect.form(state, selectors.STORE_NAME + '.badges.current')),
    badge: formSelect.data(formSelect.form(state, selectors.STORE_NAME + '.badges.current'))
  }),
  (dispatch, ownProps) =>({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp(ownProps.name, propName, propValue))
    }
  })
)(BadgeFormComponent)

export {
  BadgeForm
}
