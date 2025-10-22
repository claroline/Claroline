import React from 'react'

import {Modal} from '#/main/app/overlays'
import {useDispatch} from 'react-redux'

import {actions} from '#/main/app/platform/store'
import {OrganizationCard} from '#/main/community/organization/components/card'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

const OrganizationsModal = (props) => {
  const dispatch = useDispatch()

  return (
    <Modal
      {...props}
    >
      <div className="modal-body" role="presentation">
        <div className="d-flex flex-column gap-1" role="presentation">
          {props.organizations.map(organization =>
            <OrganizationCard
              key={organization.id}
              size="sm"
              orientation="row"
              data={organization}
              primaryAction={{
                type: CALLBACK_BUTTON,
                callback: () => {
                  dispatch(actions.changeOrganization(organization))
                  props.fadeModal()
                }
              }}
            />
          )}
        </div>
      </div>
    </Modal>
  )
}

export {
  OrganizationsModal
}
