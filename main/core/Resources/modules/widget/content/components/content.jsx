import React from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {Embedded} from '#/main/app/components/embedded'

import {WidgetInstance as WidgetInstanceTypes} from '#/main/core/widget/prop-types'
import {getWidget} from '#/main/core/widget/types'

const WidgetContent = props =>
  <Embedded
    name={`${props.type}-${props.id}`}
    load={getWidget(props.type)}
    parameters={[props.context, props.parameters]}
  />

implementPropTypes(WidgetContent, WidgetInstanceTypes, {
  context: T.object.isRequired
})

export {
  WidgetContent
}
