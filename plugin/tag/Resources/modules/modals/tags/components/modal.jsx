import React from 'react'
import omit from 'lodash/omit'

import {Modal} from '#/main/app/overlay/modal/components/modal'

const TagsModal = props =>
  <Modal
    {...omit(props)}
  >

  </Modal>

TagsModal.propTypes = {

}

TagsModal.defaultProps = {

}

export {
  TagsModal
}
