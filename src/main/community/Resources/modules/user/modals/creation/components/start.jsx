import React from 'react'
import {PropTypes as T} from 'prop-types'
import {useSelector} from 'react-redux'

import {trans} from '#/main/app/intl'
import {ASYNC_BUTTON, CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ContentMenu} from '#/main/app/content/components/menu'
import {selectors} from '#/main/app/platform/store'
import {selectors as contextSelectors} from '#/main/app/context'

import {MODAL_USERS} from '#/main/community/modals/users'

const CreationStart = (props) => {
  const currentOrganization = useSelector(selectors.currentOrganization)
  const organizations = useSelector(contextSelectors.organizations)

  return (
    <div className="modal-body" role="presentation">
      <ContentMenu
        className="mb-3"
        items={[
          {
            id: 'create-empty',
            icon: 'plus',
            label: trans('create_user', {}, 'actions'),
            description: trans('create_user_desc', {}, 'actions'),
            action: {
              type: CALLBACK_BUTTON,
              callback: props.startCreation
            }
          }, {
            id: 'create-from-organization',
            icon: 'building',
            label: trans('add_from_another_organization', {}, 'actions'),
            description: trans('add_from_another_organization_desc', {}, 'community'),
            action: {
              type: MODAL_BUTTON,
              modal: [MODAL_USERS, {
                icon: null,
                title: trans('new_user', {}, 'community'),
                subtitle: trans('add_to_organization_desc', {}, 'community'),
                multiple: true,
                filters: [
                  {property: 'organizations', value: organizations.map(o => o.id !== currentOrganization.id ? o.id : 'not:'+o.id)}
                ],
                selectAction: (selected) => ({
                  type: ASYNC_BUTTON,
                  label: trans('add_to_organization', {}, 'actions'),
                  request: {
                    url: ['apiv2_user_add_current_organization'],
                    request: {
                      method: 'PUT',
                      body: JSON.stringify(selected.map(u => u.id))
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
          }
        ]}
      />
    </div>
  )
}

CreationStart.propTypes = {
  startCreation: T.func.isRequired,
  onCreate: T.func,
  fadeModal: T.func.isRequired
}

export {
  CreationStart
}
