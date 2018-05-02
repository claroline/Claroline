import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {makeId} from '#/main/core/scaffolding/id'
import {trans, transChoice} from '#/main/core/translation'
import {select as formSelect} from '#/main/core/data/form/selectors'
import {FormContainer} from '#/main/core/data/form/containers/form.jsx'
import {MODAL_CONFIRM} from '#/main/core/layout/modal'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {FormSections, FormSection} from '#/main/core/layout/form/components/form-sections.jsx'
import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'

import {ClacoForm as ClacoFormType} from '#/plugin/claco-form/resources/claco-form/prop-types'
import {constants} from '#/plugin/claco-form/resources/claco-form/constants'
import {actions} from '#/plugin/claco-form/resources/claco-form/editor/actions'
import {MODAL_CATEGORY_FORM} from '#/plugin/claco-form/resources/claco-form/editor/components/modals/category-form-modal.jsx'
import {MODAL_KEYWORD_FORM} from '#/plugin/claco-form/resources/claco-form/editor/components/modals/keyword-form-modal.jsx'

const generateDisplayList = (fields) => {
  const displayList = {
    title: trans('title'),
    date: trans('date'),
    user: trans('user'),
    categories: trans('categories'),
    keywords: trans('keywords', {}, 'clacoform')
  }

  fields.filter(f => !f.restrictions.hidden).map(field => {
    displayList[field.id] = field.name
  })

  return displayList
}

const generateRestrictedList = (fields) => {
  const restrictedList = {
    type: trans('type'),
    title: trans('title'),
    status: trans('published'),
    locked: trans('locked'),
    createdAfter: trans('created_after'),
    createdBefore: trans('created_before'),
    user: trans('user'),
    categories: trans('categories'),
    keywords: trans('keywords', {}, 'clacoform')
  }

  fields.filter(f => !f.restrictions.hidden && ['file', 'date'].indexOf(f.type) === -1).map(field => {
    restrictedList[field.id] = field.name
  })

  return restrictedList
}

const EditorComponent = props =>
  <section className="resource-section">
    <h2>{trans('configuration', {}, 'platform')}</h2>
    <FormContainer
      level={3}
      name="clacoFormForm"
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
              name: 'details.max_entries',
              type: 'number',
              label: trans('label_max_entries', {}, 'clacoform'),
              required: true,
              options: {
                min: 0
              }
            }, {
              name: 'details.creation_enabled',
              type: 'boolean',
              label: trans('label_creation_enabled', {}, 'clacoform'),
              required: true
            }, {
              name: 'details.edition_enabled',
              type: 'boolean',
              label: trans('label_edition_enabled', {}, 'clacoform'),
              required: true
            }, {
              name: 'details.moderated',
              type: 'boolean',
              label: trans('label_moderated', {}, 'clacoform'),
              required: true
            }, {
              name: 'details.keywords_enabled',
              type: 'boolean',
              label: trans('label_keywords_enabled', {}, 'clacoform'),
              required: true,
              linked: [
                {
                  name: 'details.new_keywords_enabled',
                  type: 'boolean',
                  label: trans('label_new_keywords_enabled', {}, 'clacoform'),
                  displayed: props.clacoForm.details.keywords_enabled,
                  required: true
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
              name: 'details.display_nb_entries',
              type: 'choice',
              label: trans('label_display_nb_entries', {}, 'clacoform'),
              required: true,
              options: {
                noEmpty: true,
                condensed: true,
                choices: constants.DISPLAY_NB_ENTRIES_CHOICES
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
              name: 'details.display_categories',
              type: 'boolean',
              label: trans('label_display_categories', {}, 'clacoform'),
              required: true
            }, {
              name: 'details.display_keywords',
              type: 'boolean',
              label: trans('label_display_keywords', {}, 'clacoform'),
              required: true
            }
          ]
        }, {
          id: 'random',
          icon: 'fa fa-fw fa-random',
          title: trans('random_entries', {}, 'clacoform'),
          fields: [
            {
              name: 'details.random_enabled',
              type: 'boolean',
              label: trans('label_random_enabled', {}, 'clacoform'),
              required: true,
              linked: [
                {
                  name: 'details.random_categories',
                  type: 'choice',
                  label: trans('label_random_categories', {}, 'clacoform'),
                  displayed: props.clacoForm.details.random_enabled,
                  required: false,
                  options: {
                    multiple: true,
                    condensed: true,
                    choices: props.clacoForm.categories.reduce((acc, cat) => {
                      acc[cat.id] = cat.name

                      return acc
                    }, {})
                  }
                }, {
                  name: 'details.random_start_date',
                  type: 'date',
                  label: trans('label_random_start_date', {}, 'clacoform'),
                  displayed: props.clacoForm.details.random_enabled,
                  required: true
                },
                {
                  name: 'details.random_end_date',
                  type: 'date',
                  label: trans('label_random_end_date', {}, 'clacoform'),
                  displayed: props.clacoForm.details.random_enabled,
                  required: true
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
              label: trans('label_search_enabled', {}, 'clacoform'),
              required: true
            }, {
              name: 'details.search_column_enabled',
              type: 'boolean',
              label: trans('label_search_column_enabled', {}, 'clacoform'),
              required: true
            }, {
              name: 'details.search_columns',
              type: 'choice',
              label: trans('label_search_columns', {}, 'clacoform'),
              required: false,
              options: {
                multiple: true,
                condensed: true,
                choices: generateDisplayList(props.clacoForm.fields)
              }
            }, {
              name: 'details.search_restricted',
              type: 'boolean',
              label: trans('label_search_restricted', {}, 'clacoform'),
              required: true,
              linked: [
                {
                  name: 'details.search_restricted_columns',
                  type: 'choice',
                  label: trans('label_search_restricted_columns', {}, 'clacoform'),
                  required: false,
                  displayed: props.clacoForm.details.search_restricted,
                  options: {
                    multiple: true,
                    condensed: true,
                    choices: generateRestrictedList(props.clacoForm.fields)
                  }
                }
              ]
            }, {
              name: 'details.default_display_mode',
              type: 'choice',
              label: trans('default_display_mode', {}, 'clacoform'),
              required: true,
              options: {
                noEmpty: true,
                condensed: true,
                choices: constants.DISPLAY_MODES_CHOICES
              }
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
        }, {
          id: 'metadata',
          icon: 'fa fa-fw fa-user-secret',
          title: trans('confidential_data'),
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
            }
          ]
        }, {
          id: 'locked',
          icon: 'fa fa-fw fa-lock',
          title: trans('locked_fields', {}, 'clacoform'),
          fields: [
            {
              name: 'details.locked_fields_for',
              type: 'choice',
              label: trans('lock_fields', {}, 'clacoform'),
              required: true,
              options: {
                noEmpty: true,
                condensed: true,
                choices: constants.LOCKED_FIELDS_FOR_CHOICES
              }
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
              required: true,
              linked: [
                {
                  name: 'details.comments_roles',
                  type: 'choice',
                  label: trans('enable_comments_for_roles', {}, 'clacoform'),
                  displayed: props.clacoForm.details.comments_enabled,
                  required: false,
                  options: {
                    multiple: true,
                    condensed: true,
                    choices: props.roles.reduce((acc, r) => {
                      acc[r.name] = trans(r.translationKey)

                      return acc
                    }, {})
                  }
                }, {
                  name: 'details.moderate_comments',
                  type: 'choice',
                  label: trans('label_moderate_comments', {}, 'clacoform'),
                  displayed: props.clacoForm.details.comments_enabled,
                  required: true,
                  options: {
                    noEmpty: true,
                    condensed: true,
                    choices: constants.MODERATE_COMMENTS_CHOICES
                  }
                }, {
                  name: 'details.display_comments',
                  type: 'boolean',
                  label: trans('label_display_comments', {}, 'clacoform'),
                  displayed: props.clacoForm.details.comments_enabled,
                  required: true,
                  linked: [
                    {
                      name: 'details.comments_display_roles',
                      type: 'choice',
                      label: trans('display_comments_for_roles', {}, 'clacoform'),
                      displayed: props.clacoForm.details.comments_enabled && props.clacoForm.details.display_comments,
                      required: false,
                      options: {
                        multiple: true,
                        condensed: true,
                        choices: props.roles.reduce((acc, r) => {
                          acc[r.name] = trans(r.translationKey)

                          return acc
                        }, {})
                      }
                    }, {
                      name: 'details.open_comments',
                      type: 'boolean',
                      label: trans('label_open_panel_by_default', {}, 'clacoform'),
                      displayed: props.clacoForm.details.comments_enabled && props.clacoForm.details.display_comments,
                      required: true
                    }, {
                      name: 'details.display_comment_author',
                      type: 'boolean',
                      label: trans('label_display_comment_author', {}, 'clacoform'),
                      displayed: props.clacoForm.details.comments_enabled && props.clacoForm.details.display_comments,
                      required: true
                    }, {
                      name: 'details.display_comment_date',
                      type: 'boolean',
                      label: trans('label_display_comment_date', {}, 'clacoform'),
                      displayed: props.clacoForm.details.comments_enabled && props.clacoForm.details.display_comments,
                      required: true
                    }
                  ]
                }
              ]
            }
          ]
        }
      ]}
    >
      <FormSections level={3}>
        <FormSection
          id="clacoform-categories"
          className="embedded-list-section"
          icon="fa fa-fw fa-table"
          title={trans('categories')}
          actions={[
            {
              type: 'callback',
              icon: 'fa fa-fw fa-plus',
              label: trans('create_a_category', {}, 'clacoform'),
              callback: () => props.showModal(MODAL_CATEGORY_FORM, {
                title: trans('create_a_category', {}, 'clacoform'),
                isNew: true,
                category: {
                  id: makeId(),
                  name: '',
                  managers: [],
                  details: {
                    color: '',
                    notify_addition: true,
                    notify_edition: true,
                    notify_removal: true,
                    notify_pending_comment: true
                  }
                }
              })
            }
          ]}
        >
          <DataListContainer
            name="clacoFormForm.categories"
            fetch={{
              url: ['apiv2_clacoformcategory_list', {clacoForm: props.clacoForm.id}],
              autoload: true
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
                label: trans('managers', {}, 'clacoform'),
                displayed: true,
                renderer: (rowData) => rowData.managers.map(m => m.firstName + ' ' + m.lastName).join(', ')
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
                type: 'callback',
                icon: 'fa fa-fw fa-pencil',
                label: trans('edit'),
                callback: () => props.showModal(MODAL_CATEGORY_FORM, {
                  title: trans('edit_category', {}, 'clacoform'),
                  isNew: false,
                  category: rows[0]
                }),
                context: 'row'
              }, {
                type: 'callback',
                icon: 'fa fa-fw fa-trash-o',
                label: trans('delete'),
                dangerous: true,
                callback: () => props.deleteCategories(rows)
              }
            ]}
          />
        </FormSection>
        {props.clacoForm.details.keywords_enabled &&
          <FormSection
            id="clacoform-keywords"
            className="embedded-list-section"
            icon="fa fa-fw fa-font"
            title={trans('keywords')}
            actions={[
              {
                type: 'callback',
                icon: 'fa fa-fw fa-plus',
                label: trans('create_a_keyword', {}, 'clacoform'),
                callback: () => props.showModal(MODAL_KEYWORD_FORM, {
                  title: trans('create_a_keyword', {}, 'clacoform'),
                  isNew: true,
                  keyword: {
                    id: makeId(),
                    name: ''
                  }
                })
              }
            ]}
          >
            <DataListContainer
              name="clacoFormForm.keywords"
              fetch={{
                url: ['apiv2_clacoformkeyword_list', {clacoForm: props.clacoForm.id}],
                autoload: true
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
                  type: 'callback',
                  icon: 'fa fa-fw fa-pencil',
                  label: trans('edit'),
                  callback: () => props.showModal(MODAL_KEYWORD_FORM, {
                    title: trans('edit_keyword', {}, 'clacoform'),
                    isNew: false,
                    keyword: rows[0]
                  }),
                  context: 'row'
                }, {
                  type: 'callback',
                  icon: 'fa fa-fw fa-trash-o',
                  label: trans('delete'),
                  dangerous: true,
                  callback: () => props.deleteKeywords(rows)
                }
              ]}
            />
          </FormSection>
        }
      </FormSections>
    </FormContainer>
  </section>

EditorComponent.propTypes = {
  clacoForm: T.shape(ClacoFormType.propTypes),
  roles: T.array,
  deleteCategories: T.func.isRequired,
  deleteKeywords: T.func.isRequired,
  showModal: T.func.isRequired
}

const Editor = connect(
  (state) => ({
    clacoForm: formSelect.data(formSelect.form(state, 'clacoFormForm')),
    roles: state.roles
  }),
  (dispatch) => ({
    deleteCategories(categories) {
      dispatch(
        modalActions.showModal(MODAL_CONFIRM, {
          title: trans('objects_delete_title'),
          question: transChoice('objects_delete_question', categories.length, {'count': categories.length}, 'platform'),
          dangerous: true,
          handleConfirm: () => dispatch(actions.deleteCategories(categories))
        })
      )
    },
    deleteKeywords(keywords) {
      dispatch(
        modalActions.showModal(MODAL_CONFIRM, {
          title: trans('objects_delete_title'),
          question: transChoice('objects_delete_question', keywords.length, {'count': keywords.length}, 'platform'),
          dangerous: true,
          handleConfirm: () => dispatch(actions.deleteKeywords(keywords))
        })
      )
    },
    showModal(type, props) {
      dispatch(modalActions.showModal(type, props))
    }
  })
)(EditorComponent)

export {
  Editor
}