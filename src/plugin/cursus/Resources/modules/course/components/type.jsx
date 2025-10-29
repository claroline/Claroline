import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import {useHistory} from 'react-router-dom'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl'
import {ContentMenu} from '#/main/app/content/components/menu'
import {ASYNC_BUTTON, CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {selectors} from '#/main/app/platform/store'
import {selectors as contextSelectors} from '#/main/app/context'
import {MODAL_WORKSPACES} from '#/main/core/modals/workspaces'

import {Course as CourseTypes} from '#/plugin/cursus/prop-types'
import {MODAL_TRAINING_COURSES} from '#/plugin/cursus/modals/courses'

const CreationType = (props) => {
  const history = useHistory()
  const handleNavigation = (props, history, workspace = null, course = null) => {
    if (props.modal) {
      props.fadeModal()
    }
    if (course) {
      history.push(`${props.path}/${course.slug}`)
    } else {
      history.push(props.path + '/new')
      props.openForm(null, CourseTypes.defaultProps, workspace)
    }
  }

  const currentOrganization = useSelector(selectors.currentOrganization)
  const organizations = useSelector(contextSelectors.organizations)

  return (
    <ContentMenu
      className="mb-3"
      items={[
        {
          id: 'create-with-workspace',
          icon: 'book',
          label: trans('create_mode_workspace', {}, 'cursus'),
          description: trans('create_mode_workspace_desc', {}, 'cursus'),
          displayed: props.contextType === 'desktop',
          action: {
            type: MODAL_BUTTON,
            modal: [MODAL_WORKSPACES, {
              multiple: false,
              selectAction: (selected) => ({
                type: CALLBACK_BUTTON,
                callback: () => handleNavigation(props, history, selected[0])
              })
            }]
          }
        }, {
          id: 'create-with-model',
          icon: 'stamp',
          label: trans('create_mode_model', {}, 'cursus'),
          description: trans('create_mode_model_desc', {}, 'cursus'),
          displayed: props.contextType === 'desktop',
          action: {
            type: MODAL_BUTTON,
            modal: [MODAL_WORKSPACES, {
              url: ['apiv2_workspace_list_model'],
              multiple: false,
              selectAction: (selected) => (
                {
                  type: CALLBACK_BUTTON,
                  callback: () => handleNavigation(props, history, selected[0])
                }
              )
            }]
          }
        }, {
          id: 'create-empty',
          icon: 'graduation-cap',
          label: trans('create_mode_empty', {}, 'cursus'),
          description: trans('create_mode_empty_desc', {}, 'cursus'),
          action: {
            type: CALLBACK_BUTTON,
            callback: () => handleNavigation(props, history)
          }
        }, {
          id: 'create-from-copy',
          icon: 'clone',
          label: trans('create_mode_copy', {}, 'cursus'),
          description: trans('create_mode_copy_desc', {}, 'cursus'),
          action: {
            type: MODAL_BUTTON,
            modal: [MODAL_TRAINING_COURSES, {
              selectAction: (selected) => ({
                type: ASYNC_BUTTON,
                label: trans('copy', {}, 'actions'),
                request: {
                  url: url(['apiv2_cursus_course_copy']),
                  request: {
                    method: 'POST',
                    body: JSON.stringify(selected.length ? [selected[0].id] : [])
                  },
                  success: (course) => handleNavigation(props, history, null, course[0])
                }
              })
            }]
          },
          group: trans('from_existing_content')
        }, {
          id: 'create-from-organization',
          icon: 'building',
          label: trans('add_from_another_organization', {}, 'actions'),
          description: trans('add_from_another_organization_desc', {organization: currentOrganization.name}, 'cursus'),
          displayed: 'desktop' === props.contextType,
          group: trans('from_existing_content'),
          action: {
            type: MODAL_BUTTON,
            modal: [MODAL_TRAINING_COURSES, {
              title: trans('new_course', {}, 'cursus'),
              subtitle: trans('add_to_organization_desc', {}, 'cursus'),
              multiple: true,
              filters: [
                {property: 'organizations', value: organizations.map(o => o.id !== currentOrganization.id ? o.id : 'not:'+o.id)}
              ],
              selectAction: (selected) => ({
                type: ASYNC_BUTTON,
                label: trans('add_to_organization', {}, 'actions'),
                request: {
                  url: ['apiv2_cursus_course_add_current_organization'],
                  request: {
                    method: 'PUT',
                    body: JSON.stringify(selected.map(c => c.id))
                  },
                  success: () => {
                    if (props.onCreate) {
                      props.onCreate(selected)
                    }

                    props.fadeModal()
                  }
                }
              })
            }]
          }
        }, {
          id: 'create-from-existing',
          icon: 'graduation-cap',
          label: trans('create_mode_existing', {}, 'cursus'),
          description: trans('create_mode_existing_desc', {}, 'cursus'),
          displayed: 'workspace' === props.contextType,
          action: {
            type: MODAL_BUTTON,
            modal: [MODAL_TRAINING_COURSES, {
              url: ['apiv2_cursus_course_list_existing'],
              selectAction: (selectedCourses) => ({
                type: ASYNC_BUTTON,
                label: trans('bind', {}, 'actions'),
                request: {
                  url: url(['apiv2_cursus_course_bind_workspace', {id: (selectedCourses && selectedCourses.length > 0) ? selectedCourses[0].id : null}]),
                  request: {
                    method: 'PATCH',
                    body: JSON.stringify({
                      workspace: props.contextId
                    })
                  },
                  success: () => history.push(props.path)
                }
              })
            }]
          },
          group: trans('from_existing_content')
        }
      ]}
    />
  )
}

CreationType.propTypes = {
  path: T.string.isRequired,
  openForm: T.func,
  reset: T.func,
  contextType: T.string,
  contextId: T.string,
  modal: T.bool,
  fadeModal: T.func,
  onCreate: T.func
}

export {
  CreationType
}
