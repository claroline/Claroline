import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import omit from 'lodash/omit'

import {trans} from '#/main/core/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {actions as formActions} from '#/main/core/data/form/actions'

import {selectors} from '#/main/core/tools/home/editor/modals/creation/store'
import {TabForm} from '#/main/core/tools/home/editor/components/tab'

const CreateTabModalComponent = props =>
  <Modal
    {...omit(props,  'tab', 'saveEnabled', 'create', 'loadData', 'data')}
    icon="fa fa-fw fa-plus"
    title={trans('new_tab', {}, 'home')}
    onEntering={() => props.loadData(props.data)}
  >
    <TabForm level={5} name={selectors.STORE_NAME} />

    <Button
      className="btn modal-btn"
      type="callback"
      primary={true}
      label={trans('add', {}, 'actions')}
      disabled={!props.saveEnabled}
      callback={() => {
        props.create(props.tab)
        props.fadeModal()
      }}
    />
  </Modal>

CreateTabModalComponent.propTypes = {
  saveEnabled: T.bool.isRequired,
  data: T.shape({}),
  tab: T.shape({}),
  create: T.func,
  loadData: T.func,
  fadeModal: T.func.isRequired
}

const CreateTabModal = connect(
  (state) => ({
    tab: selectors.tab(state),
    saveEnabled: selectors.saveEnabled(state)
  }),
  (dispatch) => ({
    loadData(data) {
      dispatch(formActions.resetForm(selectors.STORE_NAME, data))
    }
  })
)(CreateTabModalComponent)

export {
  CreateTabModal
}
