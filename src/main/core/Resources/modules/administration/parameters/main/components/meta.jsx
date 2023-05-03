import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {constants} from '#/main/app/layout/sections/home/constants'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/core/administration/parameters/store'

const restrictedByDates = (parameters) => get(parameters, 'restrictions.enableDates') || !isEmpty(get(parameters, 'restrictions.dates'))
const restrictedUsersCount = (parameters) => get(parameters, 'restrictions.enableUsers') || get(parameters, 'restrictions.users')
const restrictedStorage = (parameters) => get(parameters, 'restrictions.enableStorage') || get(parameters, 'restrictions.storage')

const Meta = (props) =>
  <FormData
    level={2}
    name={selectors.FORM_NAME}
    target={['apiv2_parameters_update']}
    buttons={true}
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
      exact: true
    }}
    locked={props.lockedParameters}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'display.name',
            type: 'string',
            label: trans('name'),
            required: true
          }, {
            name: 'display.secondary_name',
            type: 'string',
            label: trans('secondary_name')
          }, {
            name: 'locales.available',
            type: 'locale',
            label: trans('available_languages'),
            required: true,
            options: {
              available: props.availableLocales,
              multiple: true
            }
          }, {
            name: 'locales.default',
            type: 'locale',
            label: trans('default_language'),
            required: true,
            options: {
              available: props.availableLocales
            }
          }, {
            name: 'intl.timezone',
            type: 'timezone',
            label: trans('timezone')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-home',
        title: trans('home'),
        fields: [
          {
            name: 'home.type',
            type: 'choice',
            label: trans('type'),
            required: true,
            options: {
              multiple: false,
              condensed: true,
              choices: constants.HOME_TYPES
            },
            linked: [
              {
                name: 'home.data',
                type: 'url',
                label: trans('url'),
                required: true,
                displayed: (data) => constants.HOME_TYPE_URL === data.home.type
              }, {
                name: 'home.data',
                type: 'html',
                label: trans('content'),
                required: true,
                displayed: (data) => constants.HOME_TYPE_HTML === data.home.type
              }
            ]
          }, {
            name: 'home.menu',
            type: 'choice',
            label: trans('tools_menu'),
            mode: 'expert',
            placeholder: trans('do_nothing'),
            options: {
              condensed: false,
              noEmpty: false,
              choices: {
                open: trans('open_tools_menu'),
                close: trans('close_tools_menu')
              }
            }
          }
        ]
      }, {
        icon: 'fa fa-fw fa-sign-in',
        title: trans('login'),
        fields: [
          {
            name: 'authentication.help',
            type: 'html',
            label: trans('message')
          }, {
            name: 'authentication.redirect_after_login_option',
            type: 'choice',
            label: trans('redirect_after_login_option'),
            options: {
              multiple: false,
              condensed: false,
              choices: {
                'DESKTOP': 'DESKTOP',
                'URL': 'URL',
                'WORKSPACE_TAG': 'WORKSPACE_TAG',
                'LAST': 'LAST'
              }
            }, linked: [{
              name: 'authentication.redirect_after_login_url',
              type: 'string',
              label: trans('redirect_after_login_url'),
              displayed: (data) => data.authentication.redirect_after_login_option === 'URL',
              hideLabel: true
            }, {
              name: 'workspace.default_tag',
              label: trans('default_workspace_tag'),
              type: 'string',
              displayed: (data) => data.authentication.redirect_after_login_option === 'WORKSPACE_TAG',
              hideLabel: true
            }]
          }, {
            name: 'registration.auto_logging',
            type: 'boolean',
            label: trans('auto_logging_after_registration')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-atlas',
        title: trans('desktop'),
        fields: [
          {
            name: 'desktop.menu',
            type: 'choice',
            label: trans('tools_menu'),
            mode: 'expert',
            placeholder: trans('do_nothing'),
            options: {
              condensed: false,
              noEmpty: false,
              choices: {
                open: trans('open_tools_menu'),
                close: trans('close_tools_menu')
              }
            }
          }
        ]
      }, {
        icon: 'fa fa-fw fa-life-ring',
        title: trans('support'),
        fields: [
          {
            name: 'help.support_email',
            label: trans('support_email'),
            type: 'email'
          }, {
            name: 'help.url',
            label: trans('help_url'),
            type: 'url'
          }
        ]
      }, {
        icon: 'fa fa-fw fa-credit-card',
        title: trans('pricing'),
        fields: [
          {
            name: 'pricing.enabled',
            label: trans('enable_pricing'),
            type: 'boolean',
            linked: [
              {
                name: 'pricing.currency',
                label: trans('currency'),
                type: 'choice',
                displayed: (params) => params.pricing && params.pricing.enabled,
                options: {
                  choices: {
                    euro: trans('currency.euro'),
                    us_dollar: trans('currency.us_dollar'),
                    chf: trans('currency.chf')
                  }
                }
              }
            ]
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
            disabled: -1 !== props.lockedParameters.indexOf('restrictions.dates'), // I need to do it manually because it's a virtual field
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
            name: 'restrictions.enableUsers',
            label: trans('restrict_users_count'),
            type: 'boolean',
            calculated: restrictedUsersCount,
            disabled: -1 !== props.lockedParameters.indexOf('restrictions.users'), // I need to do it manually because it's a virtual field
            onChange: activated => {
              if (!activated) {
                props.updateProp('restrictions.users', null)
              }
            },
            linked: [
              {
                name: 'restrictions.users',
                type: 'number',
                label: trans('users_count'),
                displayed: restrictedUsersCount,
                required: true
              }
            ]
          }, {
            name: 'restrictions.enableStorage',
            label: trans('restrict_storage'),
            type: 'boolean',
            calculated: restrictedStorage,
            disabled: -1 !== props.lockedParameters.indexOf('restrictions.storage'), // I need to do it manually because it's a virtual field
            onChange: activated => {
              if (!activated) {
                props.updateProp('restrictions.storage', null)
              }
            },
            linked: [
              {
                name: 'restrictions.storage',
                type: 'number',
                label: trans('available_storage'),
                displayed: restrictedStorage,
                required: true
              }
            ]
          }
        ]
      }
    ]}
  />

Meta.propTypes = {
  path: T.string.isRequired,
  lockedParameters: T.arrayOf(T.string).isRequired,
  updateProp: T.func.isRequired,
  availableLocales: T.object
}

export {
  Meta
}
