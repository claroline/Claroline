import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import fill from 'lodash/fill'
import omit from 'lodash/omit'
import sum from 'lodash/sum'
import times from 'lodash/times'

import {trans} from '#/main/core/translation'
import {CallbackButton} from '#/main/app/button/components/callback'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {actions as modalActions} from '#/main/app/overlay/modal/store'

import {actions} from '#/main/core/widget/editor/modals/creation/store'
import {MODAL_WIDGET_CREATION_PARAMETERS} from '#/main/core/widget/editor/modals/creation/components/parameters'

const LayoutPreview = props => {
  let ratio = []
  if (props.cols) {
    ratio = props.cols
  } else {
    ratio = fill(new Array(props.cols.length), 1)
  }

  return (
    <CallbackButton
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
const LayoutModalComponent = props =>
  <Modal
    {...omit(props, 'selectLayout', 'create')}
    icon="fa fa-fw fa-plus"
    title={trans('new_section')}
    subtitle={trans('new_section_select', {}, 'widget')}
  >
    <div className="modal-body">
      <h5>1 colonne</h5>

      <LayoutPreview cols={[1]} select={(layout) => props.selectLayout(layout, props.create)} />

      <h5>2 colonnes</h5>

      <LayoutPreview cols={[1, 1]} select={(layout) => props.selectLayout(layout, props.create)} />
      <LayoutPreview cols={[1, 2]} select={(layout) => props.selectLayout(layout, props.create)} />
      <LayoutPreview cols={[2, 1]} select={(layout) => props.selectLayout(layout, props.create)} />

      <h5>3 colonnes</h5>

      <LayoutPreview cols={[1, 1, 1]} select={(layout) => props.selectLayout(layout, props.create)} />
    </div>
  </Modal>

LayoutModalComponent.propTypes = {
  create: T.func.isRequired,
  selectLayout: T.func.isRequired,
  fadeModal: T.func.isRequired
}

const LayoutModal = connect(
  null,
  (dispatch) => ({
    selectLayout(layout, create) {
      //dispatch(actions.create()).then(close)
      dispatch(actions.startCreation(layout))

      // display the second creation modal
      dispatch(modalActions.showModal(MODAL_WIDGET_CREATION_PARAMETERS, {
        create: create
      }))
    }
  })
)(LayoutModalComponent)

export {
  LayoutModal
}
