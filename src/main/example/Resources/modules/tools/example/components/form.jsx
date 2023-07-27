import React from 'react'

import {trans} from '#/main/app/intl'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/example//tools/example/store/selectors'

const ExampleForm = (props) =>
  <ToolPage
    path={[
      {
        type: LINK_BUTTON,
        label: 'Forms',
        target: props.path+'/forms'
      }
    ]}
    subtitle="Forms"
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
              options: {long: true}
            }, {
              name: 'htmlText',
              label: 'HTML text',
              type: 'html',
              placeholder: 'My placeholder'
            }, {
              name: 'date',
              label: 'Date',
              type: 'date'
            }, {
              name: 'dateRange',
              label: 'Date range',
              type: 'date-range'
            }, {
              name: 'file',
              label: 'File upload',
              type: 'file'
            }, {
              name: 'boolean',
              label: 'This checkbox will display additional fields once checked.',
              type: 'boolean',
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
              type: 'url'
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
              type: 'username'
            }, {
              name: 'password',
              label: 'Password',
              type: 'password'
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
              type: 'icon'
            }, {
              name: 'color',
              label: 'Color',
              type: 'color'
            }
          ]
        }
      ]}
    />
  </ToolPage>

export {
  ExampleForm
}
