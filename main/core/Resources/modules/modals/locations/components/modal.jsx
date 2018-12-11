import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors} from '#/main/core/modals/locations/store'
import {LocationList} from '#/main/core/administration/user/location/components/location-list'
import {Location as LocationType} from '#/main/core/user/prop-types'

const LocationsPickerModal = props => {
  const selectAction = props.selectAction(props.selected)

  return (
    <Modal
      {...omit(props, 'url', 'selected', 'selectAction', 'resetSelect')}
      icon="fa fa-fw fa-location-arrow"
      bsSize="lg"
      onExiting={() => props.resetSelect()}
    >
      <ListData
        name={selectors.STORE_NAME}
        fetch={{
          url: props.url,
          autoload: true
        }}
        definition={LocationList.definition}
        card={LocationList.card}
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

LocationsPickerModal.propTypes = {
  url: T.oneOfType([T.string, T.array]),
  title: T.string,
  selectAction: T.func.isRequired,
  fadeModal: T.func.isRequired,
  selected: T.arrayOf(T.shape(LocationType.propTypes)).isRequired,
  resetSelect: T.func.isRequired
}

LocationsPickerModal.defaultProps = {
  url: ['apiv2_location_list'],
  title: trans('location_selector')
}

export {
  LocationsPickerModal
}
