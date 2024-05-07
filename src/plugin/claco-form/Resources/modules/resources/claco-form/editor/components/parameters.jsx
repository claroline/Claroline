import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {EditorPage} from '#/main/app/editor'
import {selectors as editorSelectors} from '#/main/core/resource/editor'

import {getTemplateHelp} from '#/plugin/claco-form/resources/claco-form/template'
import {constants} from '#/plugin/claco-form/resources/claco-form/constants'

const EditorParameters = (props) => {
  const clacoForm = useSelector(editorSelectors.resource)
  const errors = useSelector(editorSelectors.errors)

  return (
    <EditorPage
      title={trans('parameters')}
      dataPart="resource"
      definition={[
        {
          id: 'fields',
          icon: 'fa fa-fw fa-table-list',
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
          icon: 'fa fa-fw fa-home',
          title: trans('overview'),
          fields: [
            {
              name: 'details.default_home',
              type: 'choice',
              label: trans('label_default_home', {}, 'clacoform'),
              required: true,
              options: {
                noEmpty: true,
                condensed: true,
                choices: constants.DEFAULT_HOME_CHOICES
              }
            }
          ]
        }, {
          id: 'help',
          icon: 'fa fa-fw fa-circle-question',
          title: trans('help'),
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
                  help: getTemplateHelp(clacoForm.fields || []),
                  displayed: (clacoForm) => get(clacoForm, 'template.enabled'),
                  required: true,
                  onChange: (template) => props.validateTemplate(template, clacoForm.fields, errors)
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
            }, {
              name: 'display.statistics',
              type: 'boolean',
              label: trans('enable_statistics')
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
              help: trans('help_display_metadata', {}, 'clacoform'),
              required: true,
              options: {
                noEmpty: true,
                condensed: true,
                choices: constants.DISPLAY_METADATA_CHOICES
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
  )
}

EditorParameters.propTypes = {
  validateTemplate: T.func.isRequired
}

export {
  EditorParameters
}