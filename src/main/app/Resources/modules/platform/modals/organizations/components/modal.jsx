import React from 'react'

import {Modal} from '#/main/app/overlays'
import {useDispatch, useSelector} from 'react-redux'

import {actions, selectors} from '#/main/app/platform/store'
import {OrganizationCard} from '#/main/community/organization/components/card'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

const OrganizationsModal = (props) => {
  const dispatch = useDispatch()

  const availableOrganizations = useSelector(selectors.availableOrganizations)

  return (
    <Modal
      {...props}
    >
      <div className="modal-body" role="presentation">
        <div className="d-flex flex-column gap-1" role="presentation">
          {availableOrganizations.map(organization =>
            <OrganizationCard
              key={organization.id}
              size="sm"
              direction="row"
              data={organization}
              primaryAction={{
                type: CALLBACK_BUTTON,
                callback: () => {
                  dispatch(actions.setCurrentOrganizations(organization))
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
