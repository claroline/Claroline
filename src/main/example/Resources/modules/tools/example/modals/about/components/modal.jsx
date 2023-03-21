import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'

const AboutModal = (props) =>
  <Modal
    {...props}
    icon="fa fa-fw fa-circle-info"
    title={trans('about')}
    subtitle={props.name}
  >
    <div className="modal-body">
      {props.content}
    </div>
  </Modal>

AboutModal.propTypes = {
  name: T.string,
  content: T.string
}

export {
  AboutModal
}
