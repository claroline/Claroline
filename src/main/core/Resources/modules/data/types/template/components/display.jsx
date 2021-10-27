import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {route} from '#/main/core/administration/routing'

import {Template as TemplateTypes} from '#/main/core/data/types/template/prop-types'
import {TemplateCard} from '#/main/core/data/types/template/components/card'

const TemplateDisplay = (props) => props.data ?
  <TemplateCard
    data={props.data}
    size="xs"
    primaryAction={{
      type: LINK_BUTTON,
      label: trans('open', {}, 'actions'),
      target: route('templates')+'/form/'+props.data.id
    }}
  /> :
  <ContentPlaceholder
    icon="fa fa-file-alt"
    title={trans('no_template', {}, 'template')}
  />

TemplateDisplay.propTypes = {
  data: T.shape(
    TemplateTypes.propTypes
  )
}

export {
  TemplateDisplay
}
