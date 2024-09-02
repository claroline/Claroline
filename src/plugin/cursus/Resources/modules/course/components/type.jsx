import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useHistory} from 'react-router-dom'
import {trans} from '#/main/app/intl'

import {MODAL_WORKSPACES} from '#/main/core/modals/workspaces'
import {ContentMenu} from '#/main/app/content/components/menu'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Course as CourseTypes} from '#/plugin/cursus/prop-types'

const CreationType = (props) => {
  const history = useHistory()

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
                  callback: () => {
                    if (props.modal) {
                      props.fadeModal()
                    }
                    history.push(props.path + '/course/new')
                    props.startCreation(null, CourseTypes.defaultProps, selected[0])
                  }
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
                  callback: () => {
                    if (props.modal) {
                      props.fadeModal()
                    }
                    history.push(props.path + '/course/new')
                    props.startCreation(null, CourseTypes.defaultProps, selected[0])
                  }
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
            callback: () => {
              if (props.modal) {
                props.fadeModal()
              }
              history.push(props.path + '/course/new')
              props.startCreation(null, CourseTypes.defaultProps)
            }
          }
        }, {
          id: 'create-from-copy',
          icon: 'clone',
          label: trans('create_mode_copy', {}, 'cursus'),
          description: trans('create_mode_copy_desc', {}, 'cursus'),
          action: {
            type: MODAL_BUTTON,
            modal: []
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
  startCreation: T.func,
  create: T.func,
  reset: T.func,
  contextType: T.string,
  path: T.string.isRequired,
  modal: T.bool,
  fadeModal: T.func
}

export {
  CreationType
}
