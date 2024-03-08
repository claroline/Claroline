import React from 'react'

import {trans} from '#/main/app/intl'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/example//tools/example/store/selectors'

const ExampleForm = (props) =>
  <ToolPage
    title="Forms"
  >
    <FormData
      className="mt-3"
      name={selectors.FORM_NAME}
      buttons={true}
      save={{
        type: CALLBACK_BUTTON,
        label: trans('save', {}, 'actions'),
        callback: () => true
      }}
      cancel={{
        type: CALLBACK_BUTTON,
        label: trans('save', {}, 'actions'),
        callback: () => true
      }}
      definition={[
        {
          id: 'general',
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'text',
              label: 'Short text',
              type: 'string',
              required: true,
              help: 'My help text',
              placeholder: 'My placeholder'
            }, {
              name: 'longText',
              label: 'Long text',
              type: 'string',
              placeholder: 'My placeholder',
              required: true,
              options: {long: true}
            }, {
              name: 'htmlText',
              label: 'HTML text',
              type: 'html',
              placeholder: 'My placeholder',
              required: true
            }, {
              name: 'date',
              label: 'Date',
              type: 'date',
              required: true
            }, {
              name: 'datetime',
              label: 'Date & time',
              type: 'date',
              options: {time: true}
            }, {
              name: 'dateRange',
              label: 'Date range',
              type: 'date-range',
              required: true
            }, {
              name: 'boolean',
              label: 'This checkbox will display additional fields once checked.',
              type: 'boolean',
              required: true,
              linked: [
                {
                  name: 'anotherText',
                  label: 'Another text',
                  type: 'string',
                  displayed: (data) => !!data.boolean
                }
              ]
            }, {
              name: 'url',
              label: 'URL',
              type: 'url',
              required: true
            }, {
              name: 'tags',
              label: 'Tags',
              type: 'tag',
              help: [
                'This field comes from a plugin.',
                'It will simply disappear if the plugin is disabled.'
              ]
            }
          ]
        }, {
          title: 'Choices',
          subtitle: 'An additional description to better explain the role of the fields inside the section.',
          fields: [
            {
              name: 'choiceSimple',
              label: 'Simple choice',
              type: 'choice',
              required: true,
              options: {
                choices: {
                  choice1: 'Choice 1',
                  choice2: 'Choice 2',
                  choice3: 'Choice 3'
                }
              }
            }, {
              name: 'choiceInlineSimple',
              label: 'Inline simple choice',
              type: 'choice',
              required: true,
              options: {
                inline: true,
                choices: {
                  choice1: 'Choice 1',
                  choice2: 'Choice 2',
                  choice3: 'Choice 3'
                }
              }
            }, {
              name: 'choiceCondensedSimple',
              label: 'Condensed simple choice',
              help: 'Condensed choices are not really mobile friendly. You should prefer the flat version when displaying short choices list.',
              type: 'choice',
              required: true,
              options: {
                condensed: true,
                choices: {
                  choice1: 'Choice 1',
                  choice2: 'Choice 2',
                  choice3: 'Choice 3'
                }
              }
            }, {
              name: 'choiceMultiple',
              label: 'Multiple choices',
              type: 'choice',
              required: true,
              options: {
                multiple: true,
                choices: {
                  choice1: 'Choice 1',
                  choice2: 'Choice 2',
                  choice3: 'Choice 3'
                }
              }
            }, {
              name: 'choiceInlineMultiple',
              label: 'Inline multiple choices',
              type: 'choice',
              required: true,
              options: {
                inline: true,
                multiple: true,
                choices: {
                  choice1: 'Choice 1',
                  choice2: 'Choice 2',
                  choice3: 'Choice 3'
                }
              }
            }, {
              name: 'choiceCondensedMultiple',
              label: 'Condensed multiple choices',
              type: 'choice',
              required: true,
              help: 'Condensed choices are not really mobile friendly. You should prefer the flat version when displaying short choices list.',
              options: {
                multiple: true,
                condensed: true,
                choices: {
                  choice1: 'Choice 1',
                  choice2: 'Choice 2',
                  choice3: 'Choice 3'
                }
              }
            }
          ]
        }, {
          icon: 'fa fa-fw fa-power-off',
          title: 'Authentication',
          fields: [
            {
              name: 'username',
              label: 'Username',
              type: 'username',
              required: true
            }, {
              name: 'password',
              label: 'Password',
              type: 'password',
              required: true
            }
          ]
        }, {
          icon: 'fa fa-fw fa-desktop',
          title: trans('display_parameters'),
          fields: [
            {
              name: 'image',
              label: 'Image',
              type: 'image'
            }, {
              name: 'icon',
              label: 'FontAwesome icon',
              help: trans('resource_showIcon_help', {}, 'resource'),
              type: 'icon',
              required: true
            }, {
              name: 'color',
              label: 'Color',
              type: 'color',
              required: true
            }
          ]
        }, {
          title: 'Files',
          icon: 'fa fa-fw fa-file',
          fields: [
            {
              name: 'file',
              label: 'Simple file upload',
              type: 'file',
              required: true
            }, {
              name: 'files',
              label: 'Multiple files upload',
              type: 'file',
              required: true,
              options: {multiple: true}
            }, {
              name: 'image',
              label: 'Image',
              type: 'image',
              required: true
            }
          ]
        }, {
          title: 'Others',
          fields: [
            {
              name: 'country',
              label: 'Country',
              type: 'country'
            }
          ]
        }
      ]}
    />
  </ToolPage>

export {
  ExampleForm
}
