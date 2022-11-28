import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {Organization as OrganizationTypes} from '#/main/community/prop-types'
import {OrganizationList} from '#/main/community/organization/components/list'

import {selectors} from '#/main/community/modals/organizations/store'

const OrganizationsModal = props => {
  const selectAction = props.selectAction(props.selected)

  return (
    <Modal
      {...omit(props, 'selected', 'selectAction', 'reset')}
      icon="fa fa-fw fa-building"
      className="data-picker-modal"
      bsSize="lg"
      onExiting={props.reset}
    >
      <OrganizationList
        name={selectors.STORE_NAME}
        url={props.url}
        primaryAction={undefined}
        actions={undefined}
      />

      <Button
        label={trans('select', {}, 'actions')}
        {...selectAction}
        className="modal-btn btn"
        primary={true}
        disabled={0 === props.selected.length}
        onClick={props.fadeModal}
      />
    </Modal>
  )
}

OrganizationsModal.propTypes = {
  url: T.oneOfType([T.string, T.array]),
  title: T.string,
  selectAction: T.func.isRequired,
  fadeModal: T.func.isRequired,

  // from store
  selected: T.arrayOf(T.shape(
    OrganizationTypes.propTypes
  )).isRequired,
  reset: T.func.isRequired
}

OrganizationsModal.defaultProps = {
  url: ['apiv2_organization_list'],
  title: trans('organizations')
}

export {
  OrganizationsModal
}
