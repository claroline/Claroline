import React from 'react'
import {PropTypes as T} from 'prop-types'
import fill from 'lodash/fill'
import sum from 'lodash/sum'
import times from 'lodash/times'

import {transChoice} from '#/main/core/translation'
import {CallbackButton} from '#/main/app/buttons/callback/components/button'

const LayoutPreview = props => {
  let ratio = []
  if (props.cols) {
    ratio = props.cols
  } else {
    ratio = fill(new Array(props.cols.length), 1)
  }

  return (
    <CallbackButton
      id={`layout-cols-${props.cols.join('-')}`}
      className="widget-layout-preview"
      callback={() => props.select(ratio)}
    >
      <div className="row">
        {times(props.cols.length, col =>
          <div key={col} className={`widget-col col-md-${(12 / sum(ratio)) * ratio[col]}`}>
            <div className="widget-col-preview"></div>
          </div>
        )}
      </div>
    </CallbackButton>
  )
}

LayoutPreview.propTypes = {
  cols: T.arrayOf(T.number),
  select: T.func.isRequired
}

/**
 * Select the layout for a new widget.
 *
 * @param props
 * @constructor
 */
const WidgetLayout = props =>
  <div className="modal-body home-section-layout">
    <h5>{transChoice('layout_columns', 1, {count: 1}, 'widget')}</h5>

    <LayoutPreview cols={[1]} select={props.selectLayout} />

    <h5>{transChoice('layout_columns', 2, {count: 2}, 'widget')}</h5>

    <LayoutPreview cols={[1, 1]} select={props.selectLayout} />
    <LayoutPreview cols={[1, 2]} select={props.selectLayout} />
    <LayoutPreview cols={[2, 1]} select={props.selectLayout} />

    <h5>{transChoice('layout_columns', 3, {count: 3}, 'widget')}</h5>

    <LayoutPreview cols={[1, 1, 1]} select={props.selectLayout} />
  </div>

WidgetLayout.propTypes = {
  selectLayout: T.func.isRequired
}

export {
  WidgetLayout
}
