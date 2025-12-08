import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {ASYNC_BUTTON, CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ContentMenu} from '#/main/app/content/components/menu'
import {selectors} from '#/main/app/platform/store'
import {selectors as contextSelectors} from '#/main/app/context/store'

import {MODAL_BADGES} from '#/plugin/open-badge/modals/badges'

const CreationType = (props) => {
  const currentOrganization = useSelector(selectors.currentOrganization)
  const organizations = useSelector(contextSelectors.organizations)
  const contextType = useSelector(contextSelectors.type)
  const contextData = useSelector(contextSelectors.data)

  return (
    <div className="modal-body" role="presentation">
      <ContentMenu
        className="mb-3"
        items={[
          {
            id: 'create-new',
            icon: 'plus',
            label: trans('create_new', {}, 'badge'),
            description: trans('create_new_desc', {}, 'badge'),
            action: {
              type: CALLBACK_BUTTON,
              callback: () => {
                props.startCreation('workspace' === contextType ? {
                  issuer: currentOrganization,
                  workspace: contextData
                } : {
                  issuer: currentOrganization
                })
                props.changeStep('info')
              }
            },
            advanced: true
          }, {
            id: 'create-from-organization',
            icon: 'building',
            label: trans('add_from_another_organization', {}, 'actions'),
            description: trans('add_from_another_organization_desc', {organization: currentOrganization.name}, 'badge'),
            action: {
              type: MODAL_BUTTON,
              modal: [MODAL_BADGES, {
                title: trans('new_badge', {}, 'badge'),
                subtitle: trans('add_to_organization_desc', {}, 'workspace'),
                url: ['apiv2_badge_list'],
                multiple: true,
                filters: [
                  {property: 'organizations', value: organizations.map(o => o.id !== currentOrganization.id ? o.id : 'not:'+o.id)}
                ],
                selectAction: (selected) => ({
                  type: ASYNC_BUTTON,
                  label: trans('add_to_organization', {}, 'actions'),
                  request: {
                    url: ['apiv2_badge_add_current_organization'],
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
          }
        ]}
      />
    </div>
  )
}

CreationType.propTypes = {
  model: T.bool,
  startCreation: T.func.isRequired,
  changeStep: T.func.isRequired,
  onCreate: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  CreationType
}
