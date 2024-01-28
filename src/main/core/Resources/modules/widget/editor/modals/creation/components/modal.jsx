import React from 'react'
import {PropTypes as T} from 'prop-types'
import fill from 'lodash/fill'
import merge from 'lodash/merge'
import omit from 'lodash/omit'
import sum from 'lodash/sum'
import times from 'lodash/times'

import {makeId} from '#/main/core/scaffolding/id'
import {trans, transChoice} from '#/main/app/intl/translation'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {CallbackButton} from '#/main/app/buttons'

import {WidgetContainer as WidgetContainerTypes} from '#/main/core/widget/prop-types'

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

const WidgetCreationModal = (props) =>
  <Modal
    {...omit(props, 'create')}
    icon="fa fa-fw fa-plus"
    title={trans('new_section')}
    subtitle={trans('new_section_select', {}, 'widget')}
  >
    <WidgetLayout
      selectLayout={(layout) => {
        props.create(merge({}, WidgetContainerTypes.defaultProps, {
          id: makeId(),
          display: {layout: layout}
        }))

        props.fadeModal()
      }}
    />
  </Modal>

WidgetCreationModal.propTypes = {
  create: T.func.isRequired,
  fadeModal: T.func.isRequired
}

export {
  WidgetCreationModal
}
