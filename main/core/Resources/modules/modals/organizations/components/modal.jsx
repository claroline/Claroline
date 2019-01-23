import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors} from '#/main/core/modals/organizations/store'
import {Organization as OrganizationType} from '#/main/core/user/prop-types'
import {OrganizationCard} from '#/main/core/user/data/components/organization-card'

const OrganizationsModal = props => {
  const selectAction = props.selectAction(props.selected)

  return (
    <Modal
      {...omit(props, 'url', 'selected', 'selectAction', 'resetSelect')}
      icon="fa fa-fw fa-building"
      className="data-picker-modal"
      bsSize="lg"
      onExiting={() => props.resetSelect()}
    >
      <ListData
        name={selectors.STORE_NAME}
        fetch={{
          url: props.url,
          autoload: true
        }}
        definition={[
          {
            name: 'name',
            type: 'string',
            label: trans('name'),
            displayed: true,
            primary: true
          }, {
            name: 'code',
            type: 'string',
            label: trans('code')
          }, {
            name: 'parent',
            type: 'organization',
            label: trans('parent')
          }
        ]}
        card={OrganizationCard}
        display={props.display}
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
  display: T.object,
  selectAction: T.func.isRequired,
  fadeModal: T.func.isRequired,
  selected: T.arrayOf(T.shape(OrganizationType.propTypes)).isRequired,
  resetSelect: T.func.isRequired
}

OrganizationsModal.defaultProps = {
  url: ['apiv2_organization_list'],
  title: trans('organizations_selector')
}

export {
  OrganizationsModal
}
