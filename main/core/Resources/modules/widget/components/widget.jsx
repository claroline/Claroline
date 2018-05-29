import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Embedded} from '#/main/app/components/embedded'

import {WidgetInstance as WidgetInstanceTypes} from '#/main/core/widget/prop-types'
import {getWidget} from '#/main/core/widget/types'

/**
 * Loads a widget application and renders it.
 *
 * @param props
 * @constructor
 */
const Widget = props =>
  <section className={`widget ${props.instance.type}-widget`}>
    {props.instance.name &&
      <h2 className="h-first widget-title">{props.instance.name}</h2>
    }

    <Embedded
      name={`${props.instance.type}-${props.instance.id}`}
      load={getWidget(props.instance.type)}
      parameters={[props.context, props.instance.parameters]}
    />
  </section>

Widget.propTypes = {
  instance: T.shape(
    WidgetInstanceTypes.propTypes
  ).isRequired,
  context: T.object
}

export {
  Widget
}
