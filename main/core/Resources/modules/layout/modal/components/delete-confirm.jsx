import React from 'react'

import {t} from '#/main/core/translation'
import {ConfirmModal} from './confirm.jsx'

const DeleteConfirmModal = props =>
  <ConfirmModal
    icon="fa fa-fw fa-trash"
    confirmButtonText={t('delete')}
    dangerous={true}
    {...props}
  />

export {DeleteConfirmModal}
