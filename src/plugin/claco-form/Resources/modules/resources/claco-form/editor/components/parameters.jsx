import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {LINK_BUTTON} from '#/main/app/buttons'

import {getTemplateHelp} from '#/plugin/claco-form/resources/claco-form/template'
import {ClacoForm as ClacoFormTypes} from '#/plugin/claco-form/resources/claco-form/prop-types'
import {constants} from '#/plugin/claco-form/resources/claco-form/constants'

import {selectors} from '#/plugin/claco-form/resources/claco-form/editor/store'

const EditorParameters = props =>
  <FormData
    level={2}
    title={trans('parameters')}
    name={selectors.STORE_NAME}
    buttons={true}
    target={(clacoForm) => ['apiv2_clacoform_update', {id: clacoForm.id}]}
    cancel={{
      type: LINK_BUTTON,
      target: props.path,
      exact: true
    }}
    sections={[
      {
        id: 'fields',
        icon: 'fa fa-fw fa-th-list',
        title: trans('fields'),
        primary: true,
        fields: [
          {
            name: 'fields',
            type: 'fields',
            label: trans('fields_list'),
            options: {
              placeholder: trans('no_field', {}, 'clacoform')
            },
            calculated: (data) => []
              .concat(data.fields || [])
              .sort((a, b) => {
                if (get(a, 'display.order') < get(b, 'display.order')) {
                  return -1
                }

                if (get(a, 'display.order') > get(b, 'display.order')) {
                  return 1
                }

                return 0
              })
          }
        ]
      }, {
        id: 'general',
        icon: 'fa fa-fw fa-cogs',
        title: trans('general'),
        fields: [
          {
            name: 'details.helpMessage',
            label: trans('help_message', {}, 'clacoform'),
            type: 'html',
            help: trans('help_message_help', {}, 'clacoform')
          }
        ]
      }, {
        id: 'display',
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        fields: [
          {
            name: 'details.title_field_label',
            type: 'string',
            label: trans('title_field_label', {}, 'clacoform')
          }, {
            name: 'details.default_home',
            type: 'choice',
            label: trans('label_default_home', {}, 'clacoform'),
            required: true,
            options: {
              noEmpty: true,
              condensed: true,
              choices: constants.DEFAULT_HOME_CHOICES
            }
          }, {
            name: 'details.menu_position',
            type: 'choice',
            label: trans('label_menu_position', {}, 'clacoform'),
            required: true,
            options: {
              noEmpty: true,
              condensed: true,
              choices: constants.MENU_POSITION_CHOICES
            }
          }, {
            name: 'template.enabled',
            label: trans('use_template', {}, 'clacoform'),
            type: 'boolean',
            linked: [
              {
                name: 'template.content',
                type: 'html',
                label: trans('template', {}, 'clacoform'),
                help: getTemplateHelp(props.clacoForm.fields || []),
                displayed: (clacoForm) => get(clacoForm, 'template.enabled'),
                required: true,
                onChange: (template) => props.validateTemplate(template, props.clacoForm.fields, props.errors)
              }
            ]
          }, {
            name: 'display.showConfirm',
            label: trans('show_confirm', {}, 'clacoform'),
            type: 'boolean',
            help: trans('show_confirm_help', {}, 'clacoform'),
            linked: [
              {
                name: 'display.confirmMessage',
                label: trans('confirm_message', {}, 'clacoform'),
                type: 'html',
                displayed: (resource) => get(resource, 'display.showConfirm')
              }
            ]
          }, {
            name: 'display.showEntryNav',
            type: 'boolean',
            label: trans('show_entry_nav', {}, 'clacoform')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-key',
        title: trans('access_restrictions'),
        fields: [
          {
            name: 'details.display_metadata',
            type: 'choice',
            label: trans('label_display_metadata', {}, 'clacoform'),
            required: true,
            options: {
              noEmpty: true,
              condensed: true,
              choices: constants.DISPLAY_METADATA_CHOICES
            }
          }, {
            name: 'details.locked_fields_for',
            type: 'choice',
            label: trans('lock_fields', {}, 'clacoform'),
            required: true,
            options: {
              noEmpty: true,
              condensed: true,
              choices: constants.LOCKED_FIELDS_FOR_CHOICES
            }
          }, {
            name: 'details.max_entries',
            type: 'number',
            label: trans('label_max_entries', {}, 'clacoform'),
            required: true,
            options: {
              min: 0
            }
          }, {
            name: 'details.edition_enabled',
            type: 'boolean',
            label: trans('label_edition_enabled', {}, 'clacoform')
          }, {
            name: 'details.moderated',
            type: 'boolean',
            label: trans('label_moderated', {}, 'clacoform')
          }
        ]
      }
    ]}
  />

EditorParameters.propTypes = {
  path: T.string.isRequired,
  clacoForm: T.shape(
    ClacoFormTypes.propTypes
  ),
  errors: T.object,
  roles: T.array,
  validateTemplate: T.func.isRequired
}

export {
  EditorParameters
}