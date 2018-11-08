import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {HtmlText} from '#/main/core/layout/components/html-text'

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
