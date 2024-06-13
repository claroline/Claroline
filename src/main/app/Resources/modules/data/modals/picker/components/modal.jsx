import React, {useCallback, useMemo} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'
import omit from 'lodash/omit'

import {useReducer} from '#/main/app/store/reducer'
import {trans, transChoice} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {makeListReducer, actions as listActions, selectors as listSelectors} from '#/main/app/content/list/store'

const PickerModal = (props) => {
  // append list reducer to the store if not already mounted
  const reducer = useMemo(() => makeListReducer(props.name), [props.name])
  useReducer(props.name, reducer)

  const dispatch = useDispatch()
  const reset = useCallback(() => {
    dispatch(listActions.resetSelect(props.name))
    dispatch(listActions.invalidateData(props.name))
  }, [props.name])

  const selected = useSelector((state) => listSelectors.selectedFull(listSelectors.list(state, props.name)))
  const selectAction = props.selectAction(selected)

  return (
    <Modal
      {...omit(props, 'name')}
      className="data-picker-modal"
      size="xl"
      onExited={reset}
      centered={true}
      scrollable={true}
    >
      <div className="modal-body p-0">
        {props.children}
      </div>

      <div className="modal-footer">
        {selected && 0 !== selected.length &&
          <span className="" role="presentation">
            {transChoice('list_selected_count', selected.length, {count: selected.length}, 'platform')}
          </span>
        }

        <Button
          label={trans('select', {}, 'actions')}
          {...selectAction}
          className="btn btn-primary"
          disabled={0 === selected.length}
          onClick={props.fadeModal}
        />
      </div>
    </Modal>
  )
}

PickerModal.propTypes = {
  name: T.string.isRequired,
  fadeModal: T.func.isRequired,
  multiple: T.bool
}

PickerModal.defaultProps = {
  multiple: false
}

export {
  PickerModal
}
