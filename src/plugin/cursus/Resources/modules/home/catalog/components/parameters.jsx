import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {LinkButton} from '#/main/app/buttons/link'
import {route as toolRoute} from '#/main/core/tool/routing'

const CatalogTabParameters = () =>
  <LinkButton
    className="btn btn-block btn-emphasis"
    target={toolRoute('trainings')}
    primary={true}
  >
    {trans('show_catalog', {}, 'cursus')}
  </LinkButton>

export {
  CatalogTabParameters
}
