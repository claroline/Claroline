import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'

const AboutModal = props =>
  <AboutModal
    icon="fa fa-fw fa-info"
    title={trans('about')}
    {...omit(props)}
  >
    ABOUT
  </AboutModal>

AboutModal.propTypes = {

  fadeModal: T.func.isRequired
}

AboutModal.defaultProps = {

}

export {
  AboutModal
}
