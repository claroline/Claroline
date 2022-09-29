import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {LocaleInput} from '#/main/app/data/types/locale/components/input'

const LocaleModal = props =>
  <Modal
    {...omit(props, 'current', 'available')}
    title={trans('language')}
  >
    <div className="modal-body text-center">
      <LocaleInput
        value={props.current}
        available={props.available}
        onChange={(newLocale) => {
          window.location = url(['claroline_locale_change', {locale: newLocale}])
        }}
      />
    </div>
  </Modal>

LocaleModal.propTypes = {
  current: T.string.isRequired,
  available: T.arrayOf(T.string).isRequired,
  fadeModal: T.func.isRequired
}

export {
  LocaleModal
}
