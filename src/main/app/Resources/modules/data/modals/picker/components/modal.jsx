import React, {useCallback, useMemo} from 'react'
import {PropTypes as T} from 'prop-types'
import {useDispatch, useSelector} from 'react-redux'
import omit from 'lodash/omit'

import {useReducer} from '#/main/app/store/reducer'
import {trans, transChoice} from '#/main/app/intl'
import {Button} from '#/main/app/action'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {makeListReducer, actions as listActions, selectors as listSelectors} from '#/main/app/content/list/store'
import {ListData} from '#/main/app/content/list/containers/data'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

const PickerModal = (props) => {
  // append list reducer to the store if not already mounted
  const reducer = useMemo(() => makeListReducer(props.name), [props.name])
  useReducer(props.name, reducer)

  const dispatch = useDispatch()
  const reset = useCallback(() => {
    dispatch(listActions.resetSelect(props.name))
    dispatch(listActions.invalidateData(props.name))
  }, [props.name])
  const select = useCallback((row) => {
    dispatch(listActions.toggleSelect(props.name, row))
  }, [props.name])

  const selected = useSelector((state) => listSelectors.selectedFull(listSelectors.list(state, props.name)))
  let selectAction
  if (props.selectAction) {
    selectAction = props.selectAction(selected)
  }

  return (
    <Modal
      {...omit(props, 'name', 'definition', 'card', 'url', 'selectAction')}
      className="data-picker-modal"
      size="xl"
      onExited={reset}
      centered={true}
      scrollable={true}
    >
      <div className="modal-body p-0 d-flex">
        {props.definition ?
          <ListData
            fetch={{
              url: props.url,
              autoload: true
            }}
            autoFocus={true}
            name={props.name}
            definition={props.definition}
            card={props.card}
            primaryAction={(row) => {
              if (props.multiple) {
                return ({
                  type: CALLBACK_BUTTON,
                  label: trans('select', {}, 'actions'),
                  callback: () => select(row)
                })
              }

              const selectAction = props.selectAction([row])
              if (selectAction) {
                return {
                  ...selectAction,
                  onClick: () => {
                    if (props.autoClose) {
                      props.fadeModal()
                    }
                  }
                }
              }
            }}
            selectable={props.multiple}
          /> :
          props.children
        }
        {}
      </div>

      {props.children}

      {props.multiple &&
        <div className="modal-footer">
          {selected && 0 !== selected.length &&
            <span role="presentation">
              {transChoice('list_selected_count', selected.length, {count: selected.length}, 'platform')}
            </span>
          }

          {selectAction &&
            <Button
              label={trans('select', {}, 'actions')}
              {...selectAction}
              className="btn btn-primary"
              disabled={0 === selected.length}
              onClick={props.fadeModal}
            />
          }
        </div>
      }
    </Modal>
  )
}

PickerModal.propTypes = {
  name: T.string.isRequired,
  fadeModal: T.func.isRequired,
  multiple: T.bool,
  definition: T.arrayOf(T.object),
  card: T.func,
  selectAction: T.func,
  children: T.any,
  autoClose: T.bool
}

PickerModal.defaultProps = {
  multiple: true,
  autoClose: true
}

export {
  PickerModal
}
