import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import omit from 'lodash/omit'

import {trans} from '#/main/core/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {actions as formActions} from '#/main/core/data/form/actions'

import {selectors} from '#/main/core/tools/home/editor/modals/parameters/store'
import {TabForm} from '#/main/core/tools/home/editor/components/tab'

const ParametersModalComponent = props =>
  <Modal
    {...omit(props, 'tab', 'saveEnabled', 'save', 'loadTab', 'currentTabData')}
    icon="fa fa-fw fa-cog"
    title={trans('parameters')}
    subtitle={props.currentTabData.title}
    onEntering={() => props.loadTab(props.currentTabData)}
  >
    <TabForm level={5} name={selectors.STORE_NAME} />

    <Button
      className="btn modal-btn"
      type="callback"
      primary={true}
      label={trans('save', {}, 'actions')}
      disabled={!props.saveEnabled}
      callback={() => {
        props.save(props.tab)
        props.fadeModal()
      }}
    />
  </Modal>

ParametersModalComponent.propTypes = {
  saveEnabled: T.bool.isRequired,
  currentTabData: T.shape({
    title: T.string.isRequired
  }),
  tab: T.shape({}),
  save: T.func,
  loadTab: T.func,
  fadeModal: T.func.isRequired
}

const ParametersModal = connect(
  (state) => ({
    tab: selectors.tab(state),
    saveEnabled: selectors.saveEnabled(state)
  }),
  (dispatch) => ({
    loadTab(data) {
      dispatch(formActions.resetForm(selectors.STORE_NAME, data, false))
    }
  })
)(ParametersModalComponent)

export {
  ParametersModal
}
