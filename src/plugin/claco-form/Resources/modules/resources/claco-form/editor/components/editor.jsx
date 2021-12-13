import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'

import {makeId} from '#/main/core/scaffolding/id'
import {trans, transChoice} from '#/main/app/intl/translation'
import {
  actions as formActions,
  selectors as formSelect
} from '#/main/app/content/form/store'
import {FormData} from '#/main/app/content/form/containers/data'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'
import {ListData} from '#/main/app/content/list/containers/data'
import {ListForm} from '#/main/app/content/list/parameters/containers/form'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {getTemplateErrors, getTemplateHelp} from '#/plugin/claco-form/resources/claco-form/template'
import {selectors} from '#/plugin/claco-form/resources/claco-form/store'
import {ClacoForm as ClacoFormTypes} from '#/plugin/claco-form/resources/claco-form/prop-types'
import {constants} from '#/plugin/claco-form/resources/claco-form/constants'
import {actions} from '#/plugin/claco-form/resources/claco-form/editor/store'
import entriesSource from '#/plugin/claco-form/data/sources/entries'
import {MODAL_CATEGORY_FORM} from '#/plugin/claco-form/modals/category'
import {MODAL_KEYWORD_FORM} from '#/plugin/claco-form/modals/keyword'

const generateDisplayList = (fields = []) => {
  const displayList = {
    title: trans('title'),
    date: trans('date'),
    user: trans('user'),
    categories: trans('categories'),
    keywords: trans('keywords', {}, 'clacoform')
  }

  fields.filter(f => !f.restrictions.hidden).map(field => {
    displayList[field.id] = field.label
  })

  return displayList
}

const EditorComponent = props =>
  <FormData
    level={2}
    title={trans('parameters')}
    name={selectors.STORE_NAME+'.clacoFormForm'}
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
            }
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
          }, {
            name: 'details.keywords_enabled',
            type: 'boolean',
            label: trans('label_keywords_enabled', {}, 'clacoform'),
            linked: [
              {
                name: 'details.new_keywords_enabled',
                type: 'boolean',
                label: trans('label_new_keywords_enabled', {}, 'clacoform'),
                displayed: (clacoForm) => get(clacoForm, 'details.keywords_enabled')
              }
            ]
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
            name: 'details.display_categories',
            type: 'boolean',
            label: trans('label_display_categories', {}, 'clacoform'),
            help: trans('display_categories_help', {}, 'clacoform')
          }, {
            name: 'details.display_keywords',
            type: 'boolean',
            label: trans('label_display_keywords', {}, 'clacoform')
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
      }, {
        id: 'comments',
        icon: 'fa fa-fw fa-comments-o',
        title: trans('comments', {}, 'clacoform'),
        fields: [
          {
            name: 'details.comments_enabled',
            type: 'boolean',
            label: trans('label_comments_enabled', {}, 'clacoform'),
            linked: [
              {
                name: 'details.comments_roles',
                type: 'choice',
                label: trans('enable_comments_for_roles', {}, 'clacoform'),
                displayed: (clacoForm) => get(clacoForm, 'details.comments_enabled'),
                options: {
                  multiple: true,
                  condensed: true,
                  choices: props.roles.reduce((acc, r) => Object.assign(acc, {
                    [r.name]: trans(r.translationKey)
                  }), {})
                }
              }, {
                name: 'details.moderate_comments',
                type: 'choice',
                label: trans('label_moderate_comments', {}, 'clacoform'),
                displayed: (clacoForm) => get(clacoForm, 'details.comments_enabled'),
                required: true,
                options: {
                  noEmpty: true,
                  condensed: true,
                  choices: constants.MODERATE_COMMENTS_CHOICES
                }
              }
            ]
          }, {
            name: 'details.display_comments',
            type: 'boolean',
            label: trans('label_display_comments', {}, 'clacoform'),
            linked: [
              {
                name: 'details.comments_display_roles',
                type: 'choice',
                label: trans('display_comments_for_roles', {}, 'clacoform'),
                displayed: (clacoForm) => get(clacoForm, 'details.display_comments'),
                options: {
                  multiple: true,
                  condensed: true,
                  choices: props.roles.reduce((acc, r) => Object.assign(acc, {
                    [r.name]: trans(r.translationKey)
                  }), {})
                }
              }, {
                name: 'details.open_comments',
                type: 'boolean',
                label: trans('label_open_panel_by_default', {}, 'clacoform'),
                displayed: (clacoForm) => get(clacoForm, 'details.display_comments')
              }, {
                name: 'details.display_comment_author',
                type: 'boolean',
                label: trans('label_display_comment_author', {}, 'clacoform'),
                displayed: (clacoForm) => get(clacoForm, 'details.display_comments')
              }, {
                name: 'details.display_comment_date',
                type: 'boolean',
                label: trans('label_display_comment_date', {}, 'clacoform'),
                displayed: (clacoForm) => get(clacoForm, 'details.display_comments')
              }
            ]
          }
        ]
      }, {
        id: 'random',
        icon: 'fa fa-fw fa-random',
        title: trans('random_entries', {}, 'clacoform'),
        fields: [
          {
            name: 'random.enabled',
            type: 'boolean',
            label: trans('label_random_enabled', {}, 'clacoform'),
            linked: [
              {
                name: 'random.categories',
                type: 'choice',
                label: trans('label_random_categories', {}, 'clacoform'),
                displayed: (clacoForm) => get(clacoForm, 'random.enabled'),
                options: {
                  multiple: true,
                  condensed: false,
                  inline: false,
                  choices: props.clacoForm.categories ? props.clacoForm.categories.reduce((acc, cat) => Object.assign(acc, {
                    [cat.id]: cat.name
                  }), {}) : {}
                }
              }, {
                name: 'random.dates',
                type: 'date-range',
                label: trans('label_random_dates', {}, 'clacoform'),
                displayed: (clacoForm) => clacoForm.random && clacoForm.random.enabled
              }
            ]
          }
        ]
      }, {
        id: 'list',
        icon: 'fa fa-fw fa-list',
        title: trans('entries_list_search', {}, 'clacoform'),
        fields: [
          {
            name: 'details.search_enabled',
            type: 'boolean',
            label: trans('label_search_enabled', {}, 'clacoform')
          }, {
            name: 'details.display_title',
            type: 'choice',
            label: trans('field_for_title', {}, 'clacoform'),
            required: true,
            options: {
              noEmpty: true,
              condensed: true,
              choices: generateDisplayList(props.clacoForm.fields)
            }
          }, {
            name: 'details.display_subtitle',
            type: 'choice',
            label: trans('field_for_subtitle', {}, 'clacoform'),
            required: true,
            options: {
              noEmpty: true,
              condensed: true,
              choices: generateDisplayList(props.clacoForm.fields)
            }
          }, {
            name: 'details.display_content',
            type: 'choice',
            label: trans('field_for_content', {}, 'clacoform'),
            required: true,
            options: {
              noEmpty: true,
              condensed: true,
              choices: generateDisplayList(props.clacoForm.fields)
            }
          }
        ]
      }
    ]}
  >
    <ListForm
      level={3}
      name={selectors.STORE_NAME+'.clacoFormForm'}
      dataPart="list"
      list={entriesSource(props.clacoForm, true, true, true, true).parameters}
      parameters={props.clacoForm.list}
    />

    <FormSections level={3}>
      <FormSection
        id="clacoform-categories"
        className="embedded-list-section"
        icon="fa fa-fw fa-object-group"
        title={trans('categories')}
        actions={[
          {
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('create_a_category', {}, 'clacoform'),
            modal: [MODAL_CATEGORY_FORM, {
              fields: props.clacoForm.fields,
              saveCategory: (category) => props.saveCategory(props.clacoForm.id, category, true)
            }]
          }
        ]}
      >
        <ListData
          name={selectors.STORE_NAME+'.clacoFormForm.categories'}
          fetch={{
            url: ['apiv2_clacoformcategory_list', {clacoForm: props.clacoForm.id}],
            autoload: !!props.clacoForm.id
          }}
          definition={[
            {
              name: 'name',
              type: 'string',
              label: trans('name'),
              displayed: true
            }, {
              name: 'managers',
              type: 'string',
              label: trans('managers'),
              displayed: true,
              render: (rowData) => rowData.managers.map(m => m.firstName + ' ' + m.lastName).join(', ')
            }, {
              name: 'details.notify_addition',
              type: 'boolean',
              alias: 'notify_addition',
              label: trans('addition', {}, 'clacoform'),
              displayed: true,
              sortable: false
            }, {
              name: 'details.notify_edition',
              type: 'boolean',
              alias: 'notify_edition',
              label: trans('edition', {}, 'clacoform'),
              displayed: true,
              sortable: false
            }, {
              name: 'details.notify_removal',
              type: 'boolean',
              alias: 'notify_removal',
              label: trans('removal', {}, 'clacoform'),
              displayed: true,
              sortable: false
            }, {
              name: 'details.notify_pending_comment',
              type: 'boolean',
              alias: 'notify_pending_comment',
              label: trans('comment'),
              displayed: true,
              sortable: false
            }
          ]}
          actions={(rows) => [
            {
              type: MODAL_BUTTON,
              icon: 'fa fa-fw fa-pencil',
              label: trans('edit', {}, 'actions'),
              modal: [MODAL_CATEGORY_FORM, {
                category: rows[0],
                fields: props.clacoForm.fields,
                saveCategory: (category) => props.saveCategory(props.clacoForm.id, category, false)
              }],
              scope: ['object']
            }, {
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-trash-o',
              label: trans('delete', {}, 'actions'),
              dangerous: true,
              confirm: {
                title: trans('objects_delete_title'),
                message: transChoice('objects_delete_question', rows.length, {count: rows.length}, 'platform')
              },
              callback: () => props.deleteCategories(rows)
            }
          ]}
        />
      </FormSection>

      {props.clacoForm.details && props.clacoForm.details.keywords_enabled &&
        <FormSection
          id="clacoform-keywords"
          className="embedded-list-section"
          icon="fa fa-fw fa-font"
          title={trans('keywords')}
          actions={[
            {
              type: MODAL_BUTTON,
              icon: 'fa fa-fw fa-plus',
              label: trans('create_a_keyword', {}, 'clacoform'),
              modal: [MODAL_KEYWORD_FORM, {
                title: trans('create_a_keyword', {}, 'clacoform'),
                isNew: true,
                keyword: {
                  id: makeId(),
                  name: ''
                }
              }]
            }
          ]}
        >
          <ListData
            name={selectors.STORE_NAME+'.clacoFormForm.keywords'}
            fetch={{
              url: ['apiv2_clacoformkeyword_list', {clacoForm: props.clacoForm.id}],
              autoload: !!props.clacoForm.id
            }}
            definition={[
              {
                name: 'name',
                type: 'string',
                label: trans('name'),
                displayed: true
              }
            ]}
            actions={(rows) => [
              {
                type: MODAL_BUTTON,
                icon: 'fa fa-fw fa-pencil',
                label: trans('edit'),
                modal: [MODAL_KEYWORD_FORM, {
                  title: trans('edit_keyword', {}, 'clacoform'),
                  isNew: false,
                  keyword: rows[0]
                }],
                scope: ['object']
              }, {
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-trash-o',
                label: trans('delete'),
                dangerous: true,
                confirm: {
                  title: trans('objects_delete_title'),
                  message: transChoice('objects_delete_question', rows.length, {count: rows.length}, 'platform')
                },
                callback: () => props.deleteKeywords(rows)
              }
            ]}
          />
        </FormSection>
      }
    </FormSections>
  </FormData>

EditorComponent.propTypes = {
  path: T.string.isRequired,
  clacoForm: T.shape(
    ClacoFormTypes.propTypes
  ),
  errors: T.object,
  roles: T.array,
  validateTemplate: T.func.isRequired,
  saveCategory: T.func.isRequired,
  deleteCategories: T.func.isRequired,
  deleteKeywords: T.func.isRequired
}

const Editor = connect(
  (state) => ({
    path: resourceSelectors.path(state),
    clacoForm: formSelect.data(formSelect.form(state, selectors.STORE_NAME+'.clacoFormForm')),
    errors: formSelect.errors(formSelect.form(state, selectors.STORE_NAME+'.clacoFormForm')),
    roles: selectors.roles(state)
  }),
  (dispatch) => ({
    validateTemplate(template, fields, errors = {}) {
      if (template) {
        const formErrors = Object.assign({}, errors, {
          template: {content: getTemplateErrors(template, fields)}
        })

        dispatch(formActions.setErrors(selectors.STORE_NAME+'.clacoFormForm', formErrors))
      }
    },
    saveCategory(clacoFormId, category, isNew) {
      dispatch(actions.saveCategory(clacoFormId, category, isNew))
    },
    deleteCategories(categories) {
      dispatch(actions.deleteCategories(categories))
    },
    deleteKeywords(keywords) {
      dispatch(actions.deleteKeywords(keywords))
    }
  })
)(EditorComponent)

export {
  Editor
}