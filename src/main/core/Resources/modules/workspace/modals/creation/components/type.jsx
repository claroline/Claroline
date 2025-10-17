import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'
import merge from 'lodash/merge'
import pick from 'lodash/pick'

import {trans} from '#/main/app/intl'
import {makeId} from '#/main/app/utils/id'
import {ASYNC_BUTTON, CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ContentMenu} from '#/main/app/content/components/menu'
import {selectors} from '#/main/app/platform/store'
import {selectors as contextSelectors} from '#/main/app/context/store'

import {MODAL_WORKSPACES} from '#/main/core/modals/workspaces'

const CreationType = (props) => {
  const currentOrganization = useSelector(selectors.currentOrganization)
  const organizations = useSelector(contextSelectors.organizations)

  return (
    <div className="modal-body" role="presentation">
      <ContentMenu
        className="mb-3"
        items={[
          {
            id: 'create-from-model',
            icon: 'stamp',
            label: trans('create_from_model', {}, 'actions'),
            description: trans('create_from_model_desc', {}, 'actions'),
            action: {
              type: MODAL_BUTTON,
              modal: [MODAL_WORKSPACES, {
                title: trans('new_workspace', {}, 'workspace'),
                subtitle: trans('new_workspace_from_model_desc', {}, 'workspace'),
                url: ['apiv2_workspace_list_model'],
                multiple: false,
                selectAction: (selected) => ({
                  type: CALLBACK_BUTTON,
                  label: trans('create_from_model', {}, 'actions'),
                  callback: () => {
                    props.startCreation(merge({}, pick(selected[0], 'name', 'thumbnail', 'poster', 'meta'), {
                      model: selected[0],
                      meta: {model: false, personal: false, archived: false}
                    }), 'model')
                    props.changeStep('info')
                  }
                })
              }]
            }
          }, {
            id: 'create-empty',
            icon: 'book',
            label: trans('Créer un espace vide'),
            description: trans('Créez un espace vide pour pouvoir le configurer comme vous le souhaitez.'),
            displayed: false, // TODO : implement
            action: {
              type: CALLBACK_BUTTON,
              callback: () => props.changeStep('info')
            },
            advanced: true
          }, {
            id: 'create-from-copy',
            icon: 'clone',
            label: trans('copy_workspace', {}, 'actions'),
            description: trans('copy_workspace_desc', {}, 'actions'),
            action: {
              type: MODAL_BUTTON,
              modal: [MODAL_WORKSPACES, {
                title: trans('new_workspace', {}, 'workspace'),
                subtitle: trans('select_workspaces_to_copy', {}, 'workspace'),
                multiple: false,
                selectAction: (selected) => ({
                  type: CALLBACK_BUTTON,
                  label: trans('copy', {}, 'actions'),
                  callback: () => {
                    props.startCreation(merge({}, selected[0], {
                      id: makeId(),
                      meta: {model: false, personal: false}
                    }), 'copy')
                    props.changeStep('info')
                  }
                })
              }]
            },
            group: trans('from_existing_content')
          }, {
            id: 'create-from-organization',
            icon: 'building',
            label: trans('add_from_another_organization', {}, 'actions'),
            description: trans('add_from_another_organization_desc', {organization: currentOrganization.name}, 'workspace'),
            action: {
              type: MODAL_BUTTON,
              modal: [MODAL_WORKSPACES, {
                title: trans('new_workspace', {}, 'workspace'),
                subtitle: trans('add_to_organization_desc', {}, 'workspace'),
                multiple: true,
                filters: [
                  {property: 'organizations', value: organizations.map(o => o.id !== currentOrganization.id ? o.id : 'not:'+o.id)}
                ],
                selectAction: (selected) => ({
                  type: ASYNC_BUTTON,
                  label: trans('add_to_organization', {}, 'actions'),
                  request: {
                    url: ['apiv2_workspace_add_current_organization'],
                    request: {
                      method: 'PUT',
                      body: JSON.stringify(selected.map(w => w.id))
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
            },
            group: trans('from_existing_content')
          }, {
            id: 'create-from-import',
            icon: 'file-zipper',
            label: trans('import_archive', {}, 'actions'),
            description: trans('import_archive_desc', {}, 'actions'),
            action: {
              type: CALLBACK_BUTTON,
              callback: () => props.changeStep('upload')
            },
            advanced: true,
            group: trans('from_existing_content')
          }
        ]}
      />
    </div>
  )
}

CreationType.propTypes = {
  startCreation: T.func.isRequired,
  changeStep: T.func.isRequired,
  onCreate: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  CreationType
}
