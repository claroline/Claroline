import React from 'react'
import {t} from '../../utils/translate'

const Loader = () =>
  <div className="api-loader">
    <span className="fa fa-circle-o-notch fa-spin fa-fw"></span>
    <span className="sr-only">{t('loading')}</span>
  </div>

export {Loader}
