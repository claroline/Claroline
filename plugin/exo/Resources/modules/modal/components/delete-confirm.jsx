import React from 'react'
import {t} from './../../utils/translate'
import {ConfirmModal} from './confirm.jsx'

export const DeleteConfirmModal = props =>
  <ConfirmModal
    confirmButtonText={t('delete')}
    isDangerous={true}
    {...props}
  />
