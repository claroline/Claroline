import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useHistory} from 'react-router-dom'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl'
import {ContentMenu} from '#/main/app/content/components/menu'
import {ASYNC_BUTTON, CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

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
              url: ['apiv2_workspace_list_managed'],
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
          icon: 'plus-circle',
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
                    body: JSON.stringify({
                      ids: selected.length ? [selected[0].id] : [],
                      workspace: props.contextId
                    })
                  },
                  success: (course) => handleNavigation(props, history, null, course[0])
                }
              })
            }]
          },
          group: trans('create_mode_group_existing', {}, 'cursus')
        }, {
          id: 'create-from-organization',
          icon: 'building',
          label: trans('create_mode_organization', {}, 'cursus'),
          description: trans('create_mode_organization_desc', {}, 'cursus'),
          action: {
            type: CALLBACK_BUTTON,
            callback: () => true
          },
          group: trans('create_mode_group_existing', {}, 'cursus')
        }, {
          id: 'create-from-existing',
          icon: 'graduation-cap',
          label: trans('create_mode_existing', {}, 'cursus'),
          description: trans('create_mode_existing_desc', {}, 'cursus'),
          displayed: props.contextType === 'workspace',
          action: {
            type: CALLBACK_BUTTON,
            callback: () => true
          },
          group: trans('create_mode_group_existing', {}, 'cursus')
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
  contextId: T.object,
  modal: T.bool,
  fadeModal: T.func
}

export {
  CreationType
}
