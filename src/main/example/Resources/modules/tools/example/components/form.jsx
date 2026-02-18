import React from 'react'

import {trans} from '#/main/app/intl'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool'
import {FormData} from '#/main/app/content/form/containers/data'

import {selectors} from '#/main/example//tools/example/store/selectors'
import {PageContent} from '#/main/app/page'

const ExampleForm = () =>
  <ToolPage
    title="Forms"
  >
    <PageContent>
      <FormData
        className="my-5"
        name={selectors.FORM_NAME}
        buttons={true}
        onSave={() => true}
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
                name: 'longTextResize',
                label: 'Long text (Fit content)',
                type: 'string',
                placeholder: 'My placeholder',
                required: true,
                options: {long: true, autoResize: true, minRows: 1}
              }, {
                name: 'htmlText',
                label: 'HTML text',
                type: 'html',
                placeholder: 'My placeholder',
                required: true
              }, {
                name: 'date',
                icon: 'fa fa-fw fa-calendar',
                label: 'Date',
                type: 'date',
                required: true
              }, {
                name: 'datetime',
                icon: 'fa fa-fw fa-calendar',
                label: 'Date & time',
                type: 'date',
                options: {time: true}
              }, {
                name: 'dateRange',
                icon: 'fa fa-fw fa-calendar-week',
                label: 'Date range',
                help: 'Select a start and an end date to define a period.',
                type: 'date-range',
                required: true
              }, {
                name: 'time',
                label: 'Time',
                type: 'time',
                required: true
              }, {
                name: 'number',
                label: 'Number',
                type: 'number',
                required: true,
                options: {min: 0, max: 100}
              }, {
                name: 'numberUnit',
                label: 'Number with unit',
                type: 'number',
                required: true,
                options: {min: 0, max: 100, unit: 'unit'}
              }, {
                name: 'boolean',
                label: 'This checkbox will display additional fields once checked',
                help: 'This checkbox also as an additional help text.',
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
                icon: 'fa fa-fw fa-link',
                label: 'URL',
                type: 'url',
                required: true
              }, {
                name: 'tags',
                icon: 'fa fa-fw fa-tags',
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
            description: 'An additional description to better explain the role of the fields inside the section.',
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
            icon: 'fa fa-fw fa-key',
            title: 'Passwords',
            fields: [
              {
                name: 'simplePassword',
                label: 'Password simple',
                type: 'password',
                required: true,
                options: {
                  hideStrength: true,
                  disablePasswordCheck: true
                }
              }, {
                name: 'strengthPassword',
                label: 'Password with strength',
                type: 'password',
                required: true,
                options: {
                  hideStrength: false,
                  disablePasswordCheck: true
                }
              }, {
                name: 'requirementsPassword',
                label: 'Password with requirements and strength',
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
                icon: 'fa fa-fw fa-image',
                label: 'Image',
                type: 'image'
              }, {
                name: 'icon',
                label: 'FontAwesome icon',
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
            title: 'Contact',
            fields: [
              {
                name: 'email',
                icon: 'fa fa-fw fa-email',
                label: 'Email',
                type: 'email'
              }, {
                name: 'phone',
                icon: 'fa fa-fw fa-phone',
                label: 'Phone',
                type: 'phone'
              }, {
                name: 'address',
                icon: 'fa fa-fw fa-map-marker-alt',
                label: 'Address',
                type: 'address'
              }
            ]
          }, {
            title: 'Other',
            fields: [
              {
                name: 'locale',
                label: 'Locale',
                type: 'locale'
              }
            ]
          }, {
            icon: 'fa fa-fw fa-users',
            title: trans('community', {}, 'tools'),
            fields: [
              {
                name: 'users',
                type: 'user',
                icon: 'fa fa-fw fa-user',
                label: trans('users', {}, 'community'),
                help: 'Select one or multiple users.',
                options: {multiple: true}
              }, {
                name: 'groups',
                type: 'group',
                icon: 'fa fa-fw fa-users',
                label: trans('groups', {}, 'community'),
                help: 'Select one or multiple groups.',
                options: {multiple: true}
              }, {
                name: 'roles',
                type: 'role',
                icon: 'fa fa-fw fa-id-badge',
                label: trans('roles', {}, 'community'),
                help: 'Select one or multiple roles.',
                options: {multiple: true}
              }, {
                name: 'teams',
                type: 'team',
                icon: 'fa fa-fw fa-user-group',
                label: trans('teams', {}, 'community'),
                help: 'Select one or multiple teams.',
                options: {multiple: true}
              }, {
                name: 'organizations',
                type: 'organization',
                icon: 'fa fa-fw fa-building',
                label: trans('organizations', {}, 'community'),
                help: 'Select one or multiple organizations.',
                options: {multiple: true}
              }
            ]
          }
        ]}
      />
    </PageContent>
  </ToolPage>

export {
  ExampleForm
}
