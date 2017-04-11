import React from 'react'

import {t} from '#/main/core/translation'
import {ConfirmModal} from './confirm.jsx'

const DeleteConfirmModal = props =>
  <ConfirmModal
    confirmButtonText={t('delete')}
    isDangerous={true}
    {...props}
  />

export {DeleteConfirmModal}
