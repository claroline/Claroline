import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors} from '#/main/core/modals/organizations/store'
import {OrganizationList} from '#/main/core/administration/community/organization/components/organization-list'
import {Organization as OrganizationType} from '#/main/core/user/prop-types'

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
      <ListData
        name={selectors.STORE_NAME}
        fetch={{
          url: props.url,
          autoload: true
        }}
        definition={OrganizationList.definition}
        card={OrganizationList.card}
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
  selected: T.arrayOf(T.shape(OrganizationType.propTypes)).isRequired,
  reset: T.func.isRequired
}

OrganizationsModal.defaultProps = {
  url: ['apiv2_organization_list'], //apiv2_group_list_registerable = filter by current user organizations
  title: trans('organizations')
}

export {
  OrganizationsModal
}
