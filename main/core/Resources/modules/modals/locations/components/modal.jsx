import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors} from '#/main/core/modals/locations/store'
import {LocationList} from '#/main/core/administration/community/location/components/location-list'
import {Location as LocationType} from '#/main/core/user/prop-types'

const LocationsModal = props => {
  const selectAction = props.selectAction(props.selected)

  return (
    <Modal
      {...omit(props, 'url', 'selected', 'selectAction', 'resetSelect')}
      icon="fa fa-fw fa-location-arrow"
      className="data-picker-modal"
      bsSize="lg"
      onExiting={props.resetSelect}
    >
      <ListData
        name={selectors.STORE_NAME}
        fetch={{
          url: props.url,
          autoload: true
        }}
        definition={LocationList.definition}
        card={LocationList.card}
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

LocationsModal.propTypes = {
  url: T.oneOfType([T.string, T.array]),
  title: T.string,
  selectAction: T.func.isRequired,
  fadeModal: T.func.isRequired,
  selected: T.arrayOf(T.shape(LocationType.propTypes)).isRequired,
  resetSelect: T.func.isRequired
}

LocationsModal.defaultProps = {
  url: ['apiv2_location_list'],
  title: trans('locations')
}

export {
  LocationsModal
}
